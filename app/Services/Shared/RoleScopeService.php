<?php

namespace App\Services\Shared;

use App\Models\User;
use App\Repositories\Contracts\RoleScopeRepositoryInterface;
use Illuminate\Support\Str;

class RoleScopeService
{
    public function __construct(
        private readonly RoleScopeRepositoryInterface $repository,
    ) {}

    public function allowedTypesForUser(User $user): array
    {
        $text = $this->roleText($user);

        if (Str::contains($text, ['MARKETING', 'SUPERVISOR PUSAT SECURITY', 'SPV PUSAT SECURITY'])) {
            return ['SECURITY'];
        }

        if (Str::contains($text, ['DANRU SECURITY', 'CO-SCR'])) {
            return ['SECURITY'];
        }

        if (Str::contains($text, [
            'SUPERVISOR AREA',
            'SPV AREA',
            'SUPERVISOR WILAYAH',
            'SPV WILAYAH',
            'SPV-W',
            'SUPERVISOR PUSAT',
            'SPV PUSAT',
        ])) {
            return ['CLEANING SERVICE', 'LEADER'];
        }

        if (Str::contains($text, ['LEADER', 'CO-CS'])) {
            return ['CLEANING SERVICE'];
        }

        return [];
    }

    public function allowedClientIds(User $user): array
    {
        $ownClientId = (int) ($user->kerjasama?->client_id ?? 0);
        $text = $this->roleText($user);

        if ($ownClientId <= 0) {
            return [];
        }

        if (Str::contains($text, ['MARKETING', 'SUPERVISOR PUSAT SECURITY', 'SPV PUSAT SECURITY', 'SUPERVISOR PUSAT', 'SPV PUSAT'])) {
            return $this->repository->getAllClientIds();
        }

        if (Str::contains($text, ['SUPERVISOR WILAYAH', 'SPV WILAYAH', 'SPV-W'])) {
            return $this->repository->getClientIdsByRegion($ownClientId);
        }

        return [$ownClientId];
    }

    public function allowedUserIds(User $user, ?int $clientId = null, ?array $allowedClientIds = null): array
    {
        $targetClientId = $clientId ?? (int) ($user->kerjasama?->client_id ?: 0);
        $allowedClientIds ??= $this->allowedClientIds($user);
        if (! in_array($targetClientId, $allowedClientIds, true)) {
            return [];
        }

        if (Str::contains($this->roleText($user), ['SUPERVISOR WILAYAH', 'SPV WILAYAH', 'SPV-W'])) {
            return $this->repository->getUserIdsByClientIds([$targetClientId]);
        }

        $types = $this->allowedTypesForUser($user);
        $jabatanIds = $this->repository->getJabatanIdsByTypes($types);

        return $this->repository->getUserIdsByJabatanAndClient($jabatanIds, $targetClientId);
    }

    private function roleText(User $user): string
    {
        return Str::upper(trim(implode(' ', [
            (string) ($user->jabatan?->type_jabatan ?? ''),
            (string) ($user->jabatan?->name_jabatan ?? ''),
            (string) ($user->jabatan?->code_jabatan ?? ''),
        ])));
    }
}
