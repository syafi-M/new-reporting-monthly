<?php

use App\Repositories\Contracts\UploadTambahanRepositoryInterface;
use App\Services\UploadTambahanService;
use App\Services\UploadTambahanStorageService;
use App\Models\User;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

function makeUploadTambahanSvc(callable $repoSetup, ?callable $storageSetup = null): UploadTambahanService
{
    $repo    = Mockery::mock(UploadTambahanRepositoryInterface::class, $repoSetup);
    $storage = Mockery::mock(UploadTambahanStorageService::class, $storageSetup ?? fn ($m) => null);
    return new UploadTambahanService($repo, $storage);
}

function makeUploadTambahanUser(bool $canUpload = true): User
{
    $jabatan = (object) [
        'type_jabatan' => $canUpload ? 'leader' : 'staff',
        'name_jabatan' => $canUpload ? 'Leader CS' : 'Staff',
        'code_jabatan' => '',
    ];
    $kerjasama = (object) ['client_id' => 5];

    $user = new User();
    $user->forceFill(['id' => 1, 'name' => 'Test']);
    $user->setRelation('jabatan', $jabatan);
    $user->setRelation('kerjasama', $kerjasama);
    return $user;
}

// ── getUserIndexData ──────────────────────────────────────────────────────────

it('getUserIndexData returns uploads key', function () {
    $paginator = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    $user = makeUploadTambahanUser();

    $service = makeUploadTambahanSvc(function (MockInterface $m) use ($paginator) {
        $m->shouldReceive('paginateByUser')->once()->with(1)->andReturn($paginator);
    });

    $result = $service->getUserIndexData($user);

    expect($result)->toHaveKey('uploads');
});

// ── resolvePeriod ─────────────────────────────────────────────────────────────

it('resolvePeriod returns current month/year when null', function () {
    \Carbon\Carbon::setTestNow(\Carbon\Carbon::create(2026, 6, 15));

    $service = makeUploadTambahanSvc(fn ($m) => null);
    $result  = $service->resolvePeriod(null, null);

    expect($result['month'])->toBe(6)->and($result['year'])->toBe(2026);

    \Carbon\Carbon::setTestNow();
});

it('resolvePeriod returns explicit values', function () {
    $service = makeUploadTambahanSvc(fn ($m) => null);
    $result  = $service->resolvePeriod(3, 2025);

    expect($result['month'])->toBe(3)->and($result['year'])->toBe(2025);
});

// ── isSupervisorWilayah / isSupervisorArea ────────────────────────────────────

it('isSupervisorWilayah detects supervisor wilayah', function () {
    $user = new User();
    $user->setRelation('jabatan', (object) [
        'type_jabatan' => 'supervisor',
        'name_jabatan' => 'supervisor wilayah',
        'code_jabatan' => '',
    ]);

    $service = makeUploadTambahanSvc(fn ($m) => null);

    expect($service->isSupervisorWilayah($user))->toBeTrue();
});

it('isSupervisorArea detects supervisor area', function () {
    $user = new User();
    $user->setRelation('jabatan', (object) [
        'type_jabatan' => 'supervisor',
        'name_jabatan' => 'supervisor area',
        'code_jabatan' => '',
    ]);

    $service = makeUploadTambahanSvc(fn ($m) => null);

    expect($service->isSupervisorArea($user))->toBeTrue();
});

it('isSupervisorWilayah returns false for regular staff', function () {
    $user = new User();
    $user->setRelation('jabatan', (object) [
        'type_jabatan' => 'staff',
        'name_jabatan' => 'staff biasa',
        'code_jabatan' => '',
    ]);

    $service = makeUploadTambahanSvc(fn ($m) => null);

    expect($service->isSupervisorWilayah($user))->toBeFalse();
});
