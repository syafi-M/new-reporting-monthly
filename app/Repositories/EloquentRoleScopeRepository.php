<?php

namespace App\Repositories;

use App\Models\Clients;
use App\Models\Jabatan;
use App\Models\User;
use App\Repositories\Contracts\RoleScopeRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentRoleScopeRepository implements RoleScopeRepositoryInterface
{
    public function getJabatanIdsByTypes(array $types): array
    {
        if (empty($types)) {
            return [];
        }

        return Jabatan::query()
            ->whereIn(DB::raw('UPPER(type_jabatan)'), $types)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function getUserIdsByJabatanAndClient(array $jabatanIds, ?int $clientId = null): array
    {
        if (empty($jabatanIds)) {
            return [];
        }

        return User::query()
            ->whereIn('jabatan_id', $jabatanIds)
            ->when($clientId, function ($query, $value) {
                $query->whereHas('kerjasama.client', function ($q) use ($value) {
                    $q->where('id', $value);
                });
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function getUserIdsByClientIds(array $clientIds): array
    {
        $clientIds = array_values(array_filter(array_map('intval', $clientIds)));
        if (empty($clientIds)) {
            return [];
        }

        return User::query()
            ->whereHas('kerjasama', fn ($query) => $query->whereIn('client_id', $clientIds))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function getAllClientIds(): array
    {
        return Clients::query()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function getClientIdsByRegion(int $clientId): array
    {
        $baseClient = Clients::query()->find($clientId);
        if (! $baseClient) {
            return [];
        }

        $province = $this->normalizeRegion((string) $baseClient->province);
        $kabupaten = $this->normalizeRegion((string) $baseClient->kabupaten);
        if ($province === '' || $kabupaten === '') {
            return [$clientId];
        }

        return Clients::query()
            ->get(['id', 'province', 'kabupaten'])
            ->filter(fn ($client) => $this->normalizeRegion((string) $client->province) === $province
                && $this->normalizeRegion((string) $client->kabupaten) === $kabupaten
            )
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function normalizeRegion(string $value): string
    {
        $value = strtoupper(trim($value));
        $value = preg_replace('/\\b(KABUPATEN|KAB\\.?|KOTA)\\b/', '', $value) ?? $value;
        $value = preg_replace('/[^A-Z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\\s+/', ' ', $value) ?? $value);
    }
}
