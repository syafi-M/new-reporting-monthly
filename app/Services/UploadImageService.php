<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\Jabatan;
use App\Models\PendingSync;
use App\Models\UploadImage;
use App\Models\User;
use App\Repositories\Contracts\UploadImageRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadImageService
{
    protected Authenticatable $user;
    protected Carbon $now;

    public function __construct(
        Authenticatable $user,
        private readonly UploadImageRepositoryInterface $uploadImages,
        private readonly UploadImageStorageService $storage,
        ?Carbon $now = null,
    ) {
        $this->user = $user;
        $this->now = $now ?? now();
    }

    public function getIndexData(Request $request): array
    {
        $user = $request->user();

        return [
            'images' => $this->uploadImages->paginateUserUploads($user),
            'draft' => $this->uploadImages->findLatestUserDraft($user),
        ];
    }

    public function countDrafts(int $userId): int
    {
        return $this->uploadImages->countUserDraftsForMonth($userId, $this->now);
    }

    public function store(Request $request): UploadImage
    {
        $tempFiles = [];

        try {
            foreach (['img_before', 'img_proccess', 'img_final'] as $field) {
                if ($request->hasFile($field)) {
                    $tempFiles[$field] = $request->file($field)->store('temp');
                }
            }

            return DB::transaction(function () use ($request) {
                return $this->uploadImages->createUpload([
                    'user_id' => $this->user->id,
                    'clients_id' => $this->resolveClientIdForUser($request),
                    'note' => $request->note,
                    'status' => $request->status,
                    'max_data' => 14,
                    'img_before' => $this->storage->resolveImageInput($request, 'img_before', 'upload_images/before'),
                    'img_proccess' => $this->storage->resolveImageInput($request, 'img_proccess', 'upload_images/process'),
                    'img_final' => $this->storage->resolveImageInput($request, 'img_final', 'upload_images/final'),
                ]);
            });
        } catch (\Throwable $e) {
            $this->storePendingSync($request, $tempFiles);

            Log::error('message: error on UploadImageService', [
                'exception' => $e,
            ]);

            throw $e;
        }
    }

    public function storeDraft(Request $request): UploadImage
    {
        $user = $request->user();
        $clientId = $this->resolveClientIdForUser($request);

        return DB::transaction(function () use ($request, $user, $clientId) {
            $images = [
                'img_before' => 'none',
                'img_proccess' => 'none',
                'img_final' => 'none',
            ];

            foreach (
                [
                    'img_before' => 'upload_images/before',
                    'img_proccess' => 'upload_images/process',
                    'img_final' => 'upload_images/final',
                ] as $field => $folder
            ) {
                $resolved = $this->storage->resolveImageInput($request, $field, $folder);
                if ($resolved) {
                    $images[$field] = $resolved;
                }
            }

            return $this->uploadImages->createUpload([
                'user_id' => $user->id,
                'clients_id' => $clientId,
                'note' => $request->note,
                'status' => $request->status ?? 0,
                'max_data' => 14,
                ...$images,
            ]);
        });
    }

    public function updateUpload(Request $request, int $uploadId): UploadImage
    {
        $uploadImage = $this->uploadImages->findUserOwnedUpload($uploadId, $request->user());

        if (!$this->hasRequiredBeforeImage($request)) {
            throw new \RuntimeException('Gambar sebelum (before) wajib disertakan.');
        }

        if (!$this->hasRequiredFinalImage($request)) {
            throw new \RuntimeException('Gambar akhir (final) wajib disertakan.');
        }

        return DB::transaction(function () use ($request, $uploadImage) {
            $payload = [
                'note' => $request->note,
                'status' => $request->status,
            ];

            foreach (
                [
                    'img_before' => 'upload_images/before',
                    'img_proccess' => 'upload_images/process',
                    'img_final' => 'upload_images/final',
                ] as $field => $folder
            ) {
                $existingField = 'existing_' . $field;

                if ($request->hasFile($field) || $request->filled('temp_' . $field)) {
                    $payload[$field] = $this->storage->resolveImageInput($request, $field, $folder, $uploadImage->{$field});
                } elseif ($request->filled($existingField)) {
                    $payload[$field] = $request->input($existingField);
                } elseif ($request->input('type') === 'draft') {
                    $payload[$field] = 'none';
                }
            }

            return $this->uploadImages->updateUpload($uploadImage, $payload);
        });
    }

    public function deleteUserUpload(Request $request, UploadImage $uploadImage): void
    {
        $user = $request->user();

        if ((int) $uploadImage->user_id !== (int) $user->id || (int) $uploadImage->clients_id !== (int) $user->clients_id) {
            throw new ModelNotFoundException();
        }

        $this->storage->deleteUploadFiles($uploadImage);
        $this->uploadImages->deleteUpload($uploadImage);
    }

    public function getUploadImageData($dataQr = null): array
    {
        $user = auth()->user();
        $typeJabatanUser = Str::upper($user->jabatan->name_jabatan);
        $typeJabatanUser = str_replace('pusat', 'PUSAT', $typeJabatanUser);
        $isSecurity = Str::contains($typeJabatanUser, 'SUPERVISOR PUSAT SECURITY');

        if (!$isSecurity && $typeJabatanUser === 'DANRU SECURITY') {
            $type = ['SECURITY'];
        } else {
            $type = $isSecurity
                ? ['SECURITY', 'SUPERVISOR PUSAT SECURITY']
                : ['CLEANING SERVICE', 'FRONT OFFICE', 'LEADER', 'FO', 'KASIR', 'KARYAWAN', 'TAMAN', 'TEKNISI'];
        }

        $jabId = Jabatan::whereIn(DB::raw('UPPER(type_jabatan)'), $type)->pluck('id')->toArray();
        $userIds = User::select('id')->whereIn('jabatan_id', $jabId)->pluck('id');

        $allImages = UploadImage::query()
            ->where('clients_id', $this->user->kerjasama->client_id)
            ->where('status', 1)
            ->whereMonth('created_at', $this->now->month)
            ->whereYear('created_at', $this->now->year)
            ->whereIn('user_id', $userIds)
            ->latest()
            ->get();

        $approvedImages = $this->uploadImages->getMonthlyClientApprovedImages($this->user->kerjasama->client_id, $this->now);
        $totalImageCount = $approvedImages->reduce(function (int $carry, UploadImage $uploadImage) {
            foreach (['img_before', 'img_proccess', 'img_final'] as $field) {
                $value = $uploadImage->{$field};
                if (!empty($value) && $value !== 'none') {
                    $carry++;
                }
            }

            return $carry;
        }, 0);

        return [
            'uploadDraft' => $this->countDrafts($this->user->id),
            'allImages' => $allImages,
            'totalImageCount' => $totalImageCount,
            'dataQr' => $dataQr,
        ];
    }

    public function initChunkUpload(int $userId, array $validated): string
    {
        return $this->storage->initChunkUpload(
            $userId,
            $validated['field'],
            $validated['file_name'],
            (int) $validated['file_size'],
            $validated['mime_type'],
            (int) $validated['total_chunks'],
        );
    }

    public function storeChunkPart(int $userId, array $validated, Request $request): array
    {
        return $this->storage->storeChunkPart(
            $userId,
            $validated['upload_id'],
            (int) $validated['chunk_index'],
            file_get_contents($request->file('chunk')->getRealPath()),
        );
    }

    public function finalizeChunkUpload(int $userId, string $uploadId): array
    {
        return $this->storage->finalizeChunkUpload($userId, $uploadId);
    }

    public function cancelChunkUpload(int $userId, ?string $tempToken, ?string $uploadId): void
    {
        $this->storage->cancelChunkUpload($userId, $tempToken, $uploadId);
    }

    public function getPdfData(Request $request): array
    {
        return [
            'status' => true,
            'data' => $this->uploadImages->getPdfDataset(
                $request->filled('ids') ? (array) $request->ids : null,
                $request->filled('month') ? (string) $request->month : null,
            ),
            'my' => $request->month,
        ];
    }

    public function storePdf(Request $request): array
    {
        $filePath = $this->storage->storePdf($request->file('pdf'), (string) $request->month, (int) $request->client_ids);

        return [
            'status' => true,
            'message' => 'PDF stored successfully',
            'file_path' => Storage::url($filePath),
        ];
    }

    public function getAdminIndexData(Request $request): array
    {
        $filters = $request->only(['mitra', 'user', 'month', 'year']);

        return [
            'images' => $this->uploadImages->paginateAdminUploads($filters),
            'users' => !empty($filters['mitra']) ? $this->uploadImages->getUsersByMitra((int) $filters['mitra']) : collect(),
            'client' => Clients::all(),
        ];
    }

    public function updateAdminUpload(UploadImage $uploadImage, Request $request): UploadImage
    {
        $payload = [
            'clients_id' => $request->client_id,
            'note' => $request->note,
            'status' => 1,
        ];

        foreach (
            [
                'img_before' => 'upload_images/before',
                'img_proccess' => 'upload_images/process',
                'img_final' => 'upload_images/final',
            ] as $field => $folder
        ) {
            if ($request->hasFile($field)) {
                $payload[$field] = $this->storage->storeDirectImage($request->file($field), $folder, $uploadImage->{$field});
            }
        }

        return $this->uploadImages->updateUpload($uploadImage, $payload);
    }

    public function deleteAdminUpload(UploadImage $uploadImage): void
    {
        $this->storage->deleteUploadFiles($uploadImage);
        $this->uploadImages->deleteUpload($uploadImage);
    }

    public function massDeleteAdminUploads(array $ids): void
    {
        foreach ($ids as $id) {
            $uploadImage = $this->uploadImages->findAdminUploadById((int) $id);

            if ($uploadImage) {
                $this->deleteAdminUpload($uploadImage);
            }
        }
    }

    public function getUsersByMitra(int $mitraId)
    {
        return $this->uploadImages->getUsersByMitra($mitraId);
    }

    public function runOpportunisticTempCleanup(int $ttlMinutes = 30): void
    {
        $this->storage->runOpportunisticTempCleanup($ttlMinutes);
    }

    protected function storePendingSync(Request $request, array $tempFiles): void
    {
        if (!$this->user || !User::where('id', $this->user->id)->exists()) {
            return;
        }

        try {
            PendingSync::create([
                'user_id' => $this->user->id,
                'type' => 'create_post',
                'payload' => [
                    'temp_files' => $tempFiles,
                    'clients_id' => $this->resolveClientIdForUser($request),
                    'note' => $request->note,
                    'status' => $request->status,
                ],
            ]);
        } catch (\Throwable $pendingSyncException) {
            Log::warning('Pending sync insert failed', [
                'user_id' => $this->user->id,
                'error' => $pendingSyncException->getMessage(),
            ]);
        }
    }

    protected function hasRequiredBeforeImage(Request $request): bool
    {
        return $request->hasFile('img_before')
            || $request->filled('temp_img_before')
            || ($request->filled('existing_img_before') && $request->input('existing_img_before') !== 'none')
            || $request->input('type') === 'draft';
    }

    protected function hasRequiredFinalImage(Request $request): bool
    {
        return $request->hasFile('img_final')
            || $request->filled('temp_img_final')
            || ($request->filled('existing_img_final') && $request->input('existing_img_final') !== 'none')
            || $request->input('type') === 'draft';
    }

    protected function resolveClientIdForUser(Request $request): int
    {
        $authUser = $request->user();
        $clientId = (int) ($authUser?->kerjasama?->client_id ?? $authUser?->clients_id ?? 0);

        if ($clientId <= 0) {
            throw new \RuntimeException('Client pengguna tidak valid.');
        }

        return $clientId;
    }
}
