<?php

use App\Repositories\Contracts\LattersRepositoryInterface;
use App\Services\Media\LattersService;
use App\Services\Media\LetterStorageService;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

function makeLattersService(callable $repoSetup, ?callable $storageSetup = null): LattersService
{
    $repo    = Mockery::mock(LattersRepositoryInterface::class, $repoSetup);
    $storage = Mockery::mock(LetterStorageService::class, $storageSetup ?? fn ($m) => null);
    return new LattersService($repo, $storage);
}

// ── indexData ────────────────────────────────────────────────────────────────

it('indexData returns letters and covers keys', function () {
    $service = makeLattersService(function (MockInterface $m) {
        $paginator = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
        $m->shouldReceive('paginateWithCoverClient')->once()->andReturn($paginator);
        $m->shouldReceive('getAllCovers')->once()->andReturn(collect());
    });

    expect($service->indexData())->toHaveKeys(['letters', 'covers']);
});

// ── store ─────────────────────────────────────────────────────────────────────

it('store without signature calls create with original validated', function () {
    $validated = ['latter_numbers' => 'L-001', 'clients_id' => 5];

    $service = makeLattersService(
        function (MockInterface $m) use ($validated) {
            $m->shouldReceive('create')
                ->once()
                ->with(Mockery::on(fn ($arg) =>
                    $arg['latter_numbers'] === 'L-001' && $arg['clients_id'] === 5
                ))
                ->andReturn((new \App\Models\Latters())->forceFill(['id' => 1]));
        },
        function (MockInterface $m) {
            $m->shouldReceive('storeSignature')->once()->with(null)->andReturn(null);
        }
    );

    expect($service->store($validated, null)->id)->toBe(1);
});

it('store with signature injects path into validated', function () {
    $file      = Mockery::mock(UploadedFile::class);
    $validated = ['latter_numbers' => 'L-002', 'clients_id' => 3];

    $service = makeLattersService(
        function (MockInterface $m) {
            $m->shouldReceive('create')
                ->once()
                ->with(Mockery::on(fn ($arg) => $arg['signature'] === 'stored/sig.pdf'))
                ->andReturn((new \App\Models\Latters())->forceFill(['id' => 2]));
        },
        function (MockInterface $m) use ($file) {
            $m->shouldReceive('storeSignature')->once()->with($file)->andReturn('stored/sig.pdf');
        }
    );

    expect($service->store($validated, $file)->id)->toBe(2);
});

// ── update ────────────────────────────────────────────────────────────────────

it('update without signature does not override existing signature', function () {
    $latters = (new \App\Models\Latters())->forceFill(['id' => 5]);

    $service = makeLattersService(
        function (MockInterface $m) use ($latters) {
            $m->shouldReceive('findWithCoverClientOrFail')->once()->with(5)->andReturn($latters);
            $m->shouldReceive('update')
                ->once()
                ->with($latters, Mockery::on(fn ($arg) => !isset($arg['signature'])))
                ->andReturn($latters);
        },
        function (MockInterface $m) {
            $m->shouldReceive('storeSignature')->once()->with(null)->andReturn(null);
        }
    );

    expect($service->update(5, ['latter_numbers' => 'X'], null))->toBe($latters);
});

it('update with new signature injects path', function () {
    $file    = Mockery::mock(UploadedFile::class);
    $latters = (new \App\Models\Latters())->forceFill(['id' => 6]);

    $service = makeLattersService(
        function (MockInterface $m) use ($latters) {
            $m->shouldReceive('findWithCoverClientOrFail')->once()->andReturn($latters);
            $m->shouldReceive('update')
                ->once()
                ->with($latters, Mockery::on(fn ($arg) => $arg['signature'] === 'new/sig.pdf'))
                ->andReturn($latters);
        },
        function (MockInterface $m) use ($file) {
            $m->shouldReceive('storeSignature')->once()->with($file)->andReturn('new/sig.pdf');
        }
    );

    $service->update(6, [], $file);
});

// ── destroy ───────────────────────────────────────────────────────────────────

it('destroy delegates to repo deleteById', function () {
    $service = makeLattersService(function (MockInterface $m) {
        $m->shouldReceive('deleteById')->once()->with(10)->andReturn(true);
    });

    expect($service->destroy(10))->toBeTrue();
});

it('destroy returns false when repo returns false', function () {
    $service = makeLattersService(function (MockInterface $m) {
        $m->shouldReceive('deleteById')->once()->with(99)->andReturn(false);
    });

    expect($service->destroy(99))->toBeFalse();
});

// ── showById ──────────────────────────────────────────────────────────────────

it('showById delegates to repo', function () {
    $latters = (new \App\Models\Latters())->forceFill(['id' => 3]);

    $service = makeLattersService(function (MockInterface $m) use ($latters) {
        $m->shouldReceive('findWithCoverClientOrFail')->once()->with(3)->andReturn($latters);
    });

    expect($service->showById(3))->toBe($latters);
});
