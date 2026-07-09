<?php

use App\Models\ImageRate;
use App\Repositories\Contracts\ImageRateRepositoryInterface;
use App\Services\Media\ImageRateService;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

function makeImageRateService(callable $setup): ImageRateService
{
    return new ImageRateService(
        Mockery::mock(ImageRateRepositoryInterface::class, $setup)
    );
}

// ── store ────────────────────────────────────────────────────────────────────

it('store throws when n is empty', function () {
    $service = makeImageRateService(fn (MockInterface $m) => null);

    expect(fn () => $service->store(['n' => '', 'rate' => 5, 'name' => 'X']))
        ->toThrow(\RuntimeException::class, 'Target area rating tidak ditemukan.');
});

it('store throws when rate missing', function () {
    $service = makeImageRateService(function (MockInterface $m) {
        $m->shouldReceive('findUploadByAreaName')->andReturn(null);
    });

    expect(fn () => $service->store(['n' => 'Area A', 'name' => 'X']))
        ->toThrow(\RuntimeException::class, 'Rating wajib dipilih.');
});

it('store creates rating via repo', function () {
    $rate = new ImageRate();
    $rate->forceFill(['id' => 1]);

    $service = makeImageRateService(function (MockInterface $m) use ($rate) {
        $upload = new \App\Models\UploadImage();
        $upload->forceFill(['id' => 5]);
        $m->shouldReceive('findUploadByAreaName')->once()->andReturn($upload);
        $m->shouldReceive('create')->once()->andReturn($rate);
    });

    $result = $service->store(['n' => 'Area A', 'rate' => 4, 'name' => 'Bob']);

    expect($result->id)->toBe(1);
});

// ── getById ──────────────────────────────────────────────────────────────────

it('getById delegates to repo', function () {
    $rate = new ImageRate();
    $rate->forceFill(['id' => 3]);

    $service = makeImageRateService(function (MockInterface $m) use ($rate) {
        $m->shouldReceive('findOrFail')->once()->with(3)->andReturn($rate);
    });

    expect($service->getById(3))->toBe($rate);
});

// ── update ───────────────────────────────────────────────────────────────────

it('update finds then updates via repo', function () {
    $rate = new ImageRate();
    $rate->forceFill(['id' => 2]);

    $service = makeImageRateService(function (MockInterface $m) use ($rate) {
        $m->shouldReceive('findOrFail')->once()->with(2)->andReturn($rate);
        $m->shouldReceive('update')->once()->with($rate, ['rate' => 5])->andReturn($rate);
    });

    expect($service->update(2, ['rate' => 5]))->toBe($rate);
});

// ── destroy ──────────────────────────────────────────────────────────────────

it('destroy finds then deletes via repo', function () {
    $rate = new ImageRate();
    $rate->forceFill(['id' => 9]);

    $service = makeImageRateService(function (MockInterface $m) use ($rate) {
        $m->shouldReceive('findOrFail')->once()->with(9)->andReturn($rate);
        $m->shouldReceive('delete')->once()->with($rate);
    });

    $service->destroy(9);
});

// ── findUploadPreviewByName ───────────────────────────────────────────────────

it('returns null when rawName is null', function () {
    $service = makeImageRateService(fn (MockInterface $m) => null);

    expect($service->findUploadPreviewByName(null))->toBeNull();
});

it('trims and lowercases name before querying', function () {
    $service = makeImageRateService(function (MockInterface $m) {
        $m->shouldReceive('findUploadByAreaName')->once()->with('area b')->andReturn(null);
    });

    $service->findUploadPreviewByName('  Area B  ');
});
