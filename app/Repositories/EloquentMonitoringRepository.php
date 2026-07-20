<?php

namespace App\Repositories;

use App\Models\ActivityLogs;
use App\Models\Clients;
use App\Models\FixedImage;
use App\Models\Kerjasama;
use App\Models\UploadImage;
use App\Models\User;
use App\Repositories\Contracts\MonitoringRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentMonitoringRepository implements MonitoringRepositoryInterface
{
    public function getClientsLite(): Collection
    {
        return Clients::query()->select(['id', 'name'])->get();
    }

    public function getAllClients(): Collection
    {
        return Clients::query()->get();
    }

    public function getFixedGroupedSummary(int $month, int $year): Collection
    {
        return FixedImage::with(['user.divisi.jabatan', 'clients'])
            ->select(
                'clients_id',
                'user_id',
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total')
            )
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->groupBy('clients_id', 'user_id', 'month', 'year')
            ->get();
    }

    public function getClientNameById(int $id): ?string
    {
        return Clients::query()->find($id)?->name;
    }

    public function countUploadsForMonth(int $year, int $month): int
    {
        return UploadImage::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
    }

    public function latestActivities(int $limit = 7): Collection
    {
        return ActivityLogs::query()->latest()->limit($limit)->get();
    }

    public function countSessionsForMonth(int $month): int
    {
        return DB::table('sessions')
            ->whereMonth('last_activity', $month)
            ->count();
    }

    public function getUserPerformanceByMonth(int $userId, int $year): Collection
    {
        return UploadImage::query()->selectRaw("
                MONTH(created_at) as month,
                (
                    COUNT(CASE WHEN img_before IS NOT NULL AND img_before != '' AND img_before != 'none' THEN 1 END) +
                    COUNT(CASE WHEN img_proccess IS NOT NULL AND img_proccess != '' AND img_proccess != 'none' THEN 1 END) +
                    COUNT(CASE WHEN img_final IS NOT NULL AND img_final != '' AND img_final != 'none' THEN 1 END)
                ) as total
            ")
            ->where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function getUserDrafts(int $userId, int $month, int $year): Collection
    {
        return UploadImage::query()
            ->where('user_id', $userId)
            ->where('status', 0)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();
    }

    public function getApprovedUploadsByClient(int $clientId, int $month, int $year): Collection
    {
        return UploadImage::query()
            ->where('clients_id', $clientId)
            ->where('status', 1)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest()
            ->get();
    }

    public function countFixedByClientMonth(int $clientId, int $month, int $year): int
    {
        return FixedImage::query()
            ->where('clients_id', $clientId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
    }

    public function findClientBasic(int $clientId)
    {
        return Clients::query()
            ->select('id', 'name', 'address')
            ->find($clientId);
    }

    public function paginateUploadsForFixed(int $clientId, $startAt, $endAt, array $allowedUserIds, int $perPage = 6): LengthAwarePaginator
    {
        $uploadImageIdsQuery = UploadImage::query()
            ->select('id')
            ->where('clients_id', $clientId)
            ->where('status', 1)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereIn('user_id', $allowedUserIds);

        // dd($allowedUserIds);

        return UploadImage::query()
            ->select('id', 'user_id', 'clients_id', 'img_before', 'img_proccess', 'img_final', 'note', 'created_at')
            ->with([
                'fixedImage:id,upload_image_id,user_id,rating_value,rating_reason,rated_by_user_id,rated_at',
                'fixedImage.user:id,nama_lengkap',
                'fixedImage.ratedBy:id,nama_lengkap',
                'uploadRating:id,upload_image_id,rating_value,rating_reason,rated_by_user_id,rated_at',
                'uploadRating.ratedBy:id,nama_lengkap',
                'user:id,nama_lengkap',
            ])
            ->whereIn('id', $uploadImageIdsQuery)
            ->latest()
            ->paginate($perPage);
    }

    public function getFixedForScope(int $clientId, $startAt, $endAt, array $allowedUserIds): Collection
    {
        return FixedImage::query()
            ->select('upload_image_id', 'user_id', 'rating_value', 'rating_reason', 'rated_by_user_id', 'rated_at')
            ->where('clients_id', $clientId)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereIn('user_id', $allowedUserIds)
            ->get();
    }

    public function countFixedForUploadScope(int $clientId, $startAt, $endAt, array $allowedUserIds, bool $todayOnly = false): int
    {
        $uploadImageIds = UploadImage::query()
            ->select('id')
            ->where('clients_id', $clientId)
            ->where('status', 1)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereIn('user_id', $allowedUserIds);

        $query = FixedImage::query()
            ->where('clients_id', $clientId)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->whereIn('upload_image_id', $uploadImageIds);

        if ($todayOnly) {
            $query->whereDate('created_at', now()->toDateString());
        }

        return $query->count();
    }

    public function countUserFixedThisMonth(int $clientId, int $userId, $startAt, $endAt): int
    {
        return FixedImage::query()
            ->where('clients_id', $clientId)
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->count();
    }

    public function findFixedByUploadImageId(int $uploadImageId): ?FixedImage
    {
        return FixedImage::query()->where('upload_image_id', $uploadImageId)->first();
    }

    public function findFixedByUploadImageIdForScope(int $uploadImageId, int $clientId, array $allowedUserIds): ?FixedImage
    {
        return FixedImage::query()
            ->where('upload_image_id', $uploadImageId)
            ->where('clients_id', $clientId)
            ->whereIn('user_id', $allowedUserIds)
            ->first();
    }

    public function createFixed(array $payload): FixedImage
    {
        return FixedImage::query()->create($payload);
    }

    public function updateFixed(FixedImage $fixedImage, array $payload): FixedImage
    {
        $fixedImage->update($payload);

        return $fixedImage->fresh(['ratedBy:id,nama_lengkap']);
    }

    public function deleteFixedByUploadImageId(int $uploadImageId): bool
    {
        return (bool) FixedImage::query()->where('upload_image_id', $uploadImageId)->delete();
    }

    public function deleteFixedByUploadImageIdForScope(int $uploadImageId, int $clientId, array $allowedUserIds): bool
    {
        return (bool) FixedImage::query()
            ->where('upload_image_id', $uploadImageId)
            ->where('clients_id', $clientId)
            ->whereIn('user_id', $allowedUserIds)
            ->delete();
    }

    public function getClientsByProvince(string $province): Collection
    {
        return Clients::query()->whereRaw('LOWER(province) = ?', [$province])->get();
    }

    public function getUsersByJabatanNameLike(string $keyword): Collection
    {
        return User::query()->whereHas('jabatan', function ($query) use ($keyword) {
            $query->whereRaw('LOWER(name_jabatan) LIKE ?', ['%' . $keyword . '%']);
        })->get();
    }

    public function getUsersWithCountByIds(array $userIds, ?int $clientId, int $month, int $year): Collection
    {
        return User::query()->with(['jabatan', 'kerjasama.client'])
            ->whereIn('id', $userIds)
            ->when($clientId, function ($query) use ($clientId) {
                $query->whereHas('kerjasama.client', function ($q) use ($clientId) {
                    $q->where('id', $clientId);
                });
            })
            ->withCount(['fixedImages as total_per_month' => function ($q) use ($month, $year) {
                $q->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);
            }])
            ->get();
    }

    public function getTodayCountByUsers(array $userIds): Collection
    {
        return FixedImage::query()->select(
            'user_id',
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total_upload')
        )
            ->whereIn('user_id', $userIds)
            ->whereDate('created_at', now())
            ->groupBy('user_id', DB::raw('DATE(created_at)'))
            ->get();
    }

    public function getUserBasic(int $id)
    {
        return User::query()->select('id', 'nama_lengkap', 'kerjasama_id')->where('id', $id)->first();
    }

    public function getMitraByClientId(int $clientId)
    {
        return Kerjasama::query()->select('client_id')->with('client:id,name')->where('client_id', $clientId)->first();
    }

    public function getFixedByUserBetween(int $id, $startAt, $endAt): Collection
    {
        return FixedImage::query()->with(['user', 'uploadImage.user:id,nama_lengkap'])
            ->where('user_id', $id)
            ->whereBetween('created_at', [$startAt, $endAt])
            ->get();
    }

    public function getUploadsAggregate(?int $month, ?int $clientId): LengthAwarePaginator
    {
        $uploadsQuery = DB::table('upload_images')
            ->select(
                'user_id',
                'clients_id',
                DB::raw('MONTH(created_at) as month'),
                DB::raw('YEAR(created_at) as year'),
                DB::raw('COUNT(*) as total_count'),
                DB::raw("SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count")
            )
            ->groupBy('user_id', 'clients_id', 'month', 'year')
            ->orderBy('year', 'DESC')
            ->orderBy('month', 'DESC')
            ->orderBy('user_id');

        if ($month) {
            $uploadsQuery->having('month', '=', $month);
        }

        if ($clientId) {
            $uploadsQuery->where('clients_id', $clientId);
        }

        return $uploadsQuery->paginate(20);
    }

    public function getUserByIdWithDivisi(int $id)
    {
        return User::query()->with('divisi.jabatan')->findOrFail($id);
    }

    public function getUploadsByUserClientMonthYear(int $id, int $client, int $month, int $year): Collection
    {
        return UploadImage::query()->with('clients', 'user.divisi.jabatan')
            ->where('user_id', $id)
            ->where('clients_id', $client)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFixedByClientMonthYear(int $client, int $month, int $year): Collection
    {
        return FixedImage::query()->with('clients', 'user.divisi.jabatan')
            ->where('clients_id', $client)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();
    }

    public function getFixedDetailByUserPeriod(int $userId, string|int $month, string|int $year): Collection
    {
        return FixedImage::query()->with([
            'user:id,nama_lengkap,email',
            'clients:id,name,address',
            'uploadImage:id,user_id,clients_id,note,img_before,img_proccess,img_final,created_at',
            'uploadImage.user:id,nama_lengkap,email',
            'ratedBy:id,nama_lengkap',
        ])
            ->where('user_id', $userId)
            ->when($month !== 'all', function ($q) use ($month) {
                $q->whereMonth('created_at', $month);
            })
            ->when($year !== 'all', function ($q) use ($year) {
                $q->whereYear('created_at', $year);
            })
            ->latest()
            ->get();
    }

    public function getUploadsByDateAndClient(array $userIds, int $clientId, string $date): Collection
    {
        return UploadImage::query()->with('user:id,nama_lengkap,name')
            ->whereIn('user_id', $userIds)
            ->where('clients_id', $clientId)
            ->whereDate('created_at', $date)
            ->get();
    }

    public function getUploadDailyTotalsByClientAndUsers(int $clientId, array $userIds): Collection
    {
        return UploadImage::query()->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('clients_id', $clientId)
            ->when(!empty($userIds), function ($query) use ($userIds) {
                $query->whereIn('user_id', $userIds);
            })
            ->where('status', 1)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->get();
    }
}
