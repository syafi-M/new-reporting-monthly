<?php

use App\Models\User;
use App\Repositories\Contracts\RoleScopeRepositoryInterface;
use App\Services\Shared\RoleScopeService;
use Mockery\MockInterface;

function makeRoleUser(string $jabatan, string $type = ''): User
{
    $user = new User;
    $user->forceFill([
        'id' => 7,
        'kerjasama_id' => 1,
    ]);

    $user->setRelation('jabatan', (object) [
        'name_jabatan' => $jabatan,
        'type_jabatan' => $type,
        'code_jabatan' => '',
    ]);
    $user->setRelation('kerjasama', (object) ['client_id' => 13]);

    return $user;
}

it('resolves security-only scope for danru security', function () {
    $repository = Mockery::mock(RoleScopeRepositoryInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getJabatanIdsByTypes')->once()->with(['SECURITY'])->andReturn([1, 2]);
        $mock->shouldReceive('getUserIdsByJabatanAndClient')->once()->with([1, 2], 13)->andReturn([99]);
    });

    $service = new RoleScopeService($repository);

    $ids = $service->allowedUserIds(makeRoleUser('DANRU SECURITY', 'SECURITY'));

    expect($ids)->toBe([99]);
});

it('resolves marketing as security across all clients', function () {
    $repository = Mockery::mock(RoleScopeRepositoryInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getAllClientIds')->once()->andReturn([13, 14, 15]);
        $mock->shouldReceive('getJabatanIdsByTypes')->once()->with(['SECURITY'])->andReturn([8]);
        $mock->shouldReceive('getUserIdsByJabatanAndClient')->once()->with([8], 14)->andReturn([101, 102]);
    });

    $service = new RoleScopeService($repository);
    $user = makeRoleUser('MARKETING', 'MANAJEMEN');

    expect($service->allowedClientIds($user))->toBe([13, 14, 15])
        ->and($service->allowedUserIds($user, 14, [13, 14, 15]))->toBe([101, 102]);
});

it('resolves area scope as cleaning service and leader for own client', function () {
    $repository = Mockery::mock(RoleScopeRepositoryInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getJabatanIdsByTypes')->once()->with(['CLEANING SERVICE', 'LEADER'])->andReturn([9, 10]);
        $mock->shouldReceive('getUserIdsByJabatanAndClient')->once()->with([9, 10], 13)->andReturn([201]);
    });

    $service = new RoleScopeService($repository);

    expect($service->allowedUserIds(makeRoleUser('SUPERVISOR AREA', 'CLEANING SERVICE')))->toBe([201]);
});

it('resolves wilayah scope by client region', function () {
    $repository = Mockery::mock(RoleScopeRepositoryInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getClientIdsByRegion')->once()->with(13)->andReturn([13, 14]);
        $mock->shouldReceive('getUserIdsByClientIds')->once()->with([14])->andReturn([301, 302]);
    });

    $service = new RoleScopeService($repository);
    $user = makeRoleUser('SUPERVISOR WILAYAH CLEANING SERVICE', 'CLEANING SERVICE');

    expect($service->allowedClientIds($user))->toBe([13, 14])
        ->and($service->allowedUserIds($user, 14, [13, 14]))->toBe([301, 302]);
});

it('resolves supervisor pusat to cleaning service and leader across all clients', function () {
    $repository = Mockery::mock(RoleScopeRepositoryInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getAllClientIds')->once()->andReturn([13, 14]);
        $mock->shouldReceive('getJabatanIdsByTypes')->once()->with(['CLEANING SERVICE', 'LEADER'])->andReturn([9, 10]);
        $mock->shouldReceive('getUserIdsByJabatanAndClient')->once()->with([9, 10], 14)->andReturn([401]);
    });

    $service = new RoleScopeService($repository);
    $user = makeRoleUser('SPV Pusat', 'MANAJEMEN');

    expect($service->allowedClientIds($user))->toBe([13, 14])
        ->and($service->allowedUserIds($user, 14, [13, 14]))->toBe([401]);
});
