<?php

use App\Repositories\Contracts\MonitoringRepositoryInterface;
use App\Services\Monitoring\SendImageStatusService;
use App\Repositories\Contracts\AbsensiUserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

function makeSendStatusService(callable $repoSetup, ?callable $absensiSetup = null): SendImageStatusService
{
    $repo    = Mockery::mock(MonitoringRepositoryInterface::class, $repoSetup);
    $absensi = Mockery::mock(AbsensiUserRepositoryInterface::class, $absensiSetup ?? fn ($m) => null);
    return new SendImageStatusService($repo, $absensi);
}

// ── indexData ────────────────────────────────────────────────────────────────

it('indexData returns uploads, months, clients keys', function () {
    $paginator = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    $paginator->shouldReceive('getIterator')->andReturn(new ArrayIterator([]));

    $service = makeSendStatusService(
        function (MockInterface $m) use ($paginator) {
            $m->shouldReceive('getUploadsAggregate')->once()->andReturn($paginator);
            $m->shouldReceive('getAllClients')->once()->andReturn(collect());
        },
        function (MockInterface $m) {
            $m->shouldReceive('getUsersWithPosition')->once()->andReturn(collect());
        }
    );

    $result = $service->indexData([]);

    expect($result)->toHaveKeys(['uploads', 'months', 'clients'])
        ->and($result['months'])->toHaveCount(12);
});

// ── showData jabatan filter ───────────────────────────────────────────────────

it('showData filters out security uploads when user is cleaning', function () {
    $makeUpload = fn (string $jab) => (object) [
        'user' => (object) [
            'divisi' => (object) [
                'jabatan' => (object) ['name_jabatan' => $jab],
            ],
        ],
    ];

    $uploads = collect([
        $makeUpload('Cleaning Staff'),
        $makeUpload('Security Officer'),
    ]);

    $service = makeSendStatusService(function (MockInterface $m) use ($uploads) {
        $m->shouldReceive('getUploadsByUserClientMonthYear')->once()->andReturn($uploads);
        $m->shouldReceive('getFixedByClientMonthYear')->once()->andReturn(collect());
    });

    $result = $service->showData(1, 1, 6, 2026);

    // cleaning user → security filtered out → only cleaning remains
    expect($result['UploadsAll'])->toHaveCount(1)
        ->and(strtolower($result['UploadsAll']->first()->user->divisi->jabatan->name_jabatan))
            ->toContain('clean');
});

it('showData returns both keys', function () {
    $service = makeSendStatusService(function (MockInterface $m) {
        $m->shouldReceive('getUploadsByUserClientMonthYear')->once()->andReturn(collect());
        $m->shouldReceive('getFixedByClientMonthYear')->once()->andReturn(collect());
    });

    expect($service->showData(1, 1, 6, 2026))->toHaveKeys(['UploadsAll', 'fixed']);
});

// ── detailFixed ───────────────────────────────────────────────────────────────

it('detailFixed maps fixed images to array shape', function () {
    $fixed = new \App\Models\FixedImage();
    $fixed->forceFill([
        'id' => 10,
        'upload_image_id' => 5,
        'clients_id' => 2,
        'rating_value' => 4,
        'rating_reason' => 'Good',
    ]);
    // unset user_id so Eloquent won't lazy-load user relation
    $fixed->setRelation('user', null);
    $fixed->setRelation('clients', null);
    $fixed->setRelation('uploadImage', null);
    $fixed->setRelation('ratedBy', null);

    $service = makeSendStatusService(function (MockInterface $m) use ($fixed) {
        $m->shouldReceive('getFixedDetailByUserPeriod')->once()->andReturn(collect([$fixed]));
    });

    $result = $service->detailFixed(1, 6, 2026);

    expect($result)->toHaveCount(1)
        ->and($result[0])->toHaveKeys(['id', 'upload_image_id', 'rating_value']);
});
