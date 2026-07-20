<?php

namespace App\Services\Monitoring;

use App\Models\FixedImage;
use App\Models\UploadImage;
use App\Models\UploadImageRating;
use App\Repositories\Contracts\MonitoringRepositoryInterface;
use App\Services\Shared\PeriodService;
use App\Services\Shared\RoleScopeService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FixedImageService
{
    public function __construct(
        private readonly MonitoringRepositoryInterface $repository,
        private readonly RoleScopeService $roleScope,
        private readonly PeriodService $periodService,
    ) {}

    public function indexData($user): array
    {
        $allowedClientIds = $this->roleScope->allowedClientIds($user);

        return [
            'clients' => $this->repository->getClientsLite()
                ->whereIn('id', $allowedClientIds)
                ->values(),
        ];
    }

    public function createData(Request $request): array
    {
        $scope = $this->resolveScope($request);
        $user = $request->user();
        $images = $this->repository->paginateUploadsForFixed(
            $scope['client_id'],
            $scope['start_at'],
            $scope['end_at'],
            $scope['allowed_user_ids'],
        );

        $images->getCollection()->transform(function ($image) use ($user, $request) {
            $ratingSource = $image->fixedImage ?: $image->uploadRating;

            if ($image->fixedImage) {
                $image->fixedImage->can_rate = $this->canRateFixedImage($user);
                $image->fixedImage->can_edit_rating = $this->canEditRating($image->fixedImage, $request);
                $image->fixedImage->rated_by_name = $image->fixedImage->ratedBy?->nama_lengkap;
            }

            $image->rating_meta = $ratingSource ? [
                'rating_value' => $ratingSource->rating_value,
                'rating_reason' => $ratingSource->rating_reason,
                'rated_by_user_id' => $ratingSource->rated_by_user_id ? (int) $ratingSource->rated_by_user_id : null,
                'rated_by_name' => $ratingSource->ratedBy?->nama_lengkap ?? 'Belum difinalisasi',
                'rated_at' => optional($ratingSource->rated_at)->toISOString(),
                'can_edit_rating' => $this->canEditRatingByOwner((int) ($ratingSource->rated_by_user_id ?? 0), $request),
            ] : null;

            return $image;
        });

        return [
            'client' => $this->repository->findClientBasic($scope['client_id']),
            'image' => $images,
            'fixed' => $this->repository->getFixedForScope(
                $scope['client_id'],
                $scope['start_at'],
                $scope['end_at'],
                $scope['allowed_user_ids'],
            ),
            'counts' => $this->buildCounts($scope),
            'permissions' => [
                'can_rate' => $this->canRateFixedImage($user),
            ],
        ];
    }

    public function setImage(array $data, Request $request): array
    {
        try {
            $scope = $this->resolveScope($request);
            $requestUser = $request->user();
            $uploadImageId = (int) ($data['upload_image_id'] ?? 0);

            if ($uploadImageId <= 0) {
                return [
                    'success' => false,
                    'limit' => false,
                    'message' => 'Upload image tidak valid.',
                ];
            }

            $uploadImage = $this->resolveUploadImageInScope($uploadImageId, $scope);
            if (! $uploadImage) {
                return [
                    'success' => false,
                    'limit' => false,
                    'message' => 'Upload image tidak ditemukan atau tidak termasuk cakupan akses.',
                ];
            }

            $clientId = (int) $uploadImage->clients_id;
            $userId = (int) $uploadImage->user_id;

            $countUpload = $this->repository->countUserFixedThisMonth($clientId, $userId, $scope['start_at'], $scope['end_at']);

            if ($countUpload >= 11) {
                return [
                    'success' => false,
                    'limit' => true,
                    'message' => 'Limit Memilih Foto Tercapai!',
                ];
            }

            $existing = $this->repository->findFixedByUploadImageIdForScope(
                $uploadImageId,
                $clientId,
                $scope['allowed_user_ids'],
            );
            $model = $existing ?: $this->repository->createFixed([
                'user_id' => $userId,
                'clients_id' => $clientId,
                'upload_image_id' => $uploadImageId,
            ]);

            $ratingValue = trim((string) ($data['rating_value'] ?? ''));
            $ratingReason = trim((string) ($data['rating_reason'] ?? '')) ?: null;
            $rating = null;
            if ($ratingValue !== '' && in_array($ratingValue, ['kurang', 'cukup', 'baik'], true) && $this->canRateFixedImage($requestUser)) {
                $ratingResult = $this->persistUploadImageRating($uploadImageId, $ratingValue, $ratingReason, $requestUser);
                if (! $ratingResult['status']) {
                    return [
                        'success' => false,
                        'limit' => false,
                        'message' => $ratingResult['message'],
                    ];
                }
                $rating = $ratingResult['data'];
            }

            if ($rating) {
                $model = $this->repository->updateFixed($model, [
                    'rating_value' => $rating->rating_value,
                    'rating_reason' => $rating->rating_reason,
                    'rated_by_user_id' => (int) $rating->rated_by_user_id,
                    'rated_at' => $rating->rated_at ?? now(),
                ]);
            }

            return [
                'success' => true,
                'limit' => false,
                'message' => $existing ? 'Data sudah dipilih sebelumnya.' : 'Data Has Been Saved!',
                'data' => $model,
                'counts' => $this->buildCounts($scope),
                'rating' => $this->ratingPayload($model, $requestUser),
            ];
        } catch (Exception $e) {
            throw new Exception('Error Processing Request: '.$e->getMessage());
        }
    }

    public function removeSelection(int $uploadImageId, Request $request): array
    {
        $scope = $this->resolveScope($request);
        $uploadImage = $this->resolveUploadImageInScope($uploadImageId, $scope);

        if (! $uploadImage) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan atau tidak termasuk cakupan akses',
            ];
        }

        $deleted = $this->repository->deleteFixedByUploadImageIdForScope(
            $uploadImageId,
            (int) $uploadImage->clients_id,
            $scope['allowed_user_ids'],
        );

        if (! $deleted) {
            return [
                'status' => false,
                'message' => 'Data tidak ditemukan',
            ];
        }

        return [
            'status' => true,
            'message' => 'Data berhasil dihapus',
            'counts' => $this->buildCounts($scope),
        ];
    }

    public function countFixed(Request $request): array
    {
        $scope = $this->resolveScope($request);
        $counts = $this->buildCounts($scope);

        return [
            'count' => $counts['total_fixed'],
            'count_today' => $counts['count_today'],
        ];
    }

    public function rateImage(array $data, Request $request): array
    {
        $scope = $this->resolveScope($request);
        $user = $request->user();
        $uploadImageId = (int) ($data['upload_image_id'] ?? 0);

        if ($uploadImageId <= 0) {
            return [
                'status' => false,
                'message' => 'Upload image tidak valid.',
                'code' => 422,
            ];
        }

        if (! $this->canRateFixedImage($user)) {
            return [
                'status' => false,
                'message' => 'Anda tidak memiliki akses untuk menilai foto ini.',
                'code' => 403,
            ];
        }

        $uploadImage = $this->resolveUploadImageInScope($uploadImageId, $scope);
        if (! $uploadImage) {
            return [
                'status' => false,
                'message' => 'Upload image tidak ditemukan atau tidak termasuk cakupan akses.',
                'code' => 404,
            ];
        }

        $ratingResult = $this->persistUploadImageRating(
            $uploadImageId,
            $data['rating_value'],
            trim((string) ($data['rating_reason'] ?? '')) ?: null,
            $user,
        );
        if (! $ratingResult['status']) {
            return [
                'status' => false,
                'message' => $ratingResult['message'],
                'code' => 403,
            ];
        }

        $savedRating = $ratingResult['data'];

        $fixed = $this->repository->findFixedByUploadImageIdForScope(
            $uploadImageId,
            (int) $uploadImage->clients_id,
            $scope['allowed_user_ids'],
        );
        if ($fixed) {
            $fixed = $this->repository->updateFixed($fixed, [
                'rating_value' => $savedRating->rating_value,
                'rating_reason' => $savedRating->rating_reason,
                'rated_by_user_id' => (int) $savedRating->rated_by_user_id,
                'rated_at' => $savedRating->rated_at ?? now(),
            ]);
        } else {
            $fixed = new FixedImage([
                'upload_image_id' => $uploadImageId,
                'rating_value' => $savedRating->rating_value,
                'rating_reason' => $savedRating->rating_reason,
                'rated_by_user_id' => (int) $savedRating->rated_by_user_id,
                'rated_at' => $savedRating->rated_at ?? now(),
            ]);
            $fixed->setRelation('ratedBy', $savedRating->ratedBy);
        }

        return [
            'status' => true,
            'message' => 'Penilaian foto berhasil disimpan.',
            'code' => 200,
            'data' => $this->ratingPayload($fixed, $user),
        ];
    }

    private function resolveScope(Request $request): array
    {
        $user = $request->user();
        $allowedClientIds = $this->roleScope->allowedClientIds($user);
        $requestedClientId = (int) $request->input('client_id');
        $fallbackClientId = (int) ($user?->kerjasama?->client_id ?? 0);
        $clientId = in_array($requestedClientId, $allowedClientIds, true)
            ? $requestedClientId
            : (in_array($fallbackClientId, $allowedClientIds, true)
                ? $fallbackClientId
                : (int) ($allowedClientIds[0] ?? 0));

        $period = $this->periodService->monthRange(
            $request->filled('month') ? (int) $request->input('month') : null,
            $request->filled('year') ? (int) $request->input('year') : null,
        );

        return [
            'client_id' => $clientId,
            'start_at' => $period['start_at'],
            'end_at' => $period['end_at'],
            'allowed_client_ids' => $allowedClientIds,
            'allowed_user_ids' => $this->roleScope->allowedUserIds($user, $clientId, $allowedClientIds),
        ];
    }

    private function buildCounts(array $scope): array
    {
        return [
            'total_fixed' => $this->repository->countFixedForUploadScope(
                $scope['client_id'],
                $scope['start_at'],
                $scope['end_at'],
                $scope['allowed_user_ids'],
                false,
            ),
            'count_today' => $this->repository->countFixedForUploadScope(
                $scope['client_id'],
                $scope['start_at'],
                $scope['end_at'],
                $scope['allowed_user_ids'],
                true,
            ),
        ];
    }

    private function resolveUploadImageInScope(int $uploadImageId, array $scope): ?UploadImage
    {
        return UploadImage::query()
            ->where('id', $uploadImageId)
            ->where('clients_id', $scope['client_id'])
            ->where('status', 1)
            ->whereBetween('created_at', [$scope['start_at'], $scope['end_at']])
            ->whereIn('user_id', $scope['allowed_user_ids'])
            ->first();
    }

    private function canRateFixedImage($user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        $divisiName = Str::upper(trim((string) ($user->divisi?->name ?? '')));
        $jabatanName = Str::upper(trim((string) ($user->jabatan?->name_jabatan ?? '')));

        return in_array($divisiName, ['LEADER', 'KOMANDAN SECURITY'], true)
            || in_array($jabatanName, ['LEADER', 'KOMANDAN SECURITY'], true);
    }

    public function canCurrentUserRate(Request $request): bool
    {
        return $this->canRateFixedImage($request->user());
    }

    public function canEditRating(FixedImage $fixedImage, Request $request): bool
    {
        $user = $request->user();
        if (! $this->canRateFixedImage($user)) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        if (! $fixedImage->rated_by_user_id) {
            return true;
        }

        return (int) $fixedImage->rated_by_user_id === (int) $user?->id;
    }

    private function canEditRatingByOwner(int $ratedByUserId, Request $request): bool
    {
        $user = $request->user();
        if (! $this->canRateFixedImage($user)) {
            return false;
        }
        if ($this->isAdmin($user)) {
            return true;
        }
        if ($ratedByUserId <= 0) {
            return true;
        }

        return $ratedByUserId === (int) ($user?->id ?? 0);
    }

    public function ratingPayload(FixedImage $fixedImage, $requestUser): array
    {
        return [
            'upload_image_id' => (int) $fixedImage->upload_image_id,
            'rating_value' => $fixedImage->rating_value,
            'rating_reason' => $fixedImage->rating_reason,
            'rated_by_user_id' => $fixedImage->rated_by_user_id ? (int) $fixedImage->rated_by_user_id : null,
            'rated_at' => optional($fixedImage->rated_at)->toISOString(),
            'rated_by_name' => $fixedImage->ratedBy?->nama_lengkap,
            'can_rate' => $this->canRateFixedImage($requestUser),
            'can_edit_rating' => $requestUser
                ? $this->isAdmin($requestUser) || ! $fixedImage->rated_by_user_id
                || (int) $fixedImage->rated_by_user_id === (int) $requestUser?->id
                : false,
        ];
    }

    private function isAdmin($user): bool
    {
        return (int) ($user?->role_id ?? 0) === 2;
    }

    private function persistUploadImageRating(int $uploadImageId, string $ratingValue, ?string $ratingReason, $user): array
    {
        $existing = UploadImageRating::query()
            ->where('upload_image_id', $uploadImageId)
            ->first();

        if ($existing && ! $this->isAdmin($user) && (int) $existing->rated_by_user_id !== (int) $user->id) {
            return [
                'status' => false,
                'message' => 'Nilai hanya dapat diubah oleh pembuat nilai awal.',
            ];
        }

        if ($existing) {
            $existing->update([
                'rating_value' => $ratingValue,
                'rating_reason' => $ratingReason,
                'rated_by_user_id' => (int) $user->id,
                'rated_at' => now(),
            ]);

            return [
                'status' => true,
                'data' => $existing->fresh(['ratedBy:id,nama_lengkap']),
            ];
        }

        $created = UploadImageRating::query()->create([
            'upload_image_id' => $uploadImageId,
            'rating_value' => $ratingValue,
            'rating_reason' => $ratingReason,
            'rated_by_user_id' => (int) $user->id,
            'rated_at' => now(),
        ]);

        return [
            'status' => true,
            'data' => $created->fresh(['ratedBy:id,nama_lengkap']),
        ];
    }
}
