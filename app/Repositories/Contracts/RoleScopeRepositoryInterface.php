<?php

namespace App\Repositories\Contracts;

interface RoleScopeRepositoryInterface
{
    public function getJabatanIdsByTypes(array $types): array;

    public function getUserIdsByJabatanAndClient(array $jabatanIds, ?int $clientId = null): array;

    public function getUserIdsByClientIds(array $clientIds): array;

    public function getAllClientIds(): array;

    public function getClientIdsByRegion(int $clientId): array;
}
