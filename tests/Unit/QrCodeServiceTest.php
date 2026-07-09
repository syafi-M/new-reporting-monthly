<?php

use App\Repositories\Contracts\QrCodeRepositoryInterface;
use App\Services\Media\QrCodeService;
use App\Services\Media\QrCodeStorageService;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

function makeQrSvc(callable $repoSetup, ?callable $storageSetup = null): QrCodeService
{
    $repo    = Mockery::mock(QrCodeRepositoryInterface::class, $repoSetup);
    $storage = Mockery::mock(QrCodeStorageService::class, $storageSetup ?? fn ($m) => null);
    return new QrCodeService($repo, $storage);
}

// ── kegiatanOptions ───────────────────────────────────────────────────────────

it('kegiatanOptions merges stored + default options unique case-insensitive', function () {
    $service = makeQrSvc(function (MockInterface $m) {
        // same as a default entry in different case → should deduplicate
        $m->shouldReceive('allDataValues')->once()->andReturn(collect(['Area X - Progres fogging']));
    });

    $options = $service->kegiatanOptions();

    // default list has 'Progres fogging', stored has same → should appear once
    $lower = array_map('mb_strtolower', $options);
    expect(array_count_values($lower)['progres fogging'])->toBe(1);
});

it('kegiatanOptions returns sorted array', function () {
    $service = makeQrSvc(function (MockInterface $m) {
        $m->shouldReceive('allDataValues')->once()->andReturn(collect());
    });

    $options = $service->kegiatanOptions();
    $sorted  = $options;
    sort($sorted);

    expect($options)->toBe($sorted);
});

// ── getById ───────────────────────────────────────────────────────────────────

it('getById delegates to repo', function () {
    $qr = new \App\Models\qrCode();
    $qr->forceFill(['id' => 3]);

    $service = makeQrSvc(function (MockInterface $m) use ($qr) {
        $m->shouldReceive('findOrFail')->once()->with(3)->andReturn($qr);
    });

    expect($service->getById(3))->toBe($qr);
});

// ── delete ────────────────────────────────────────────────────────────────────

it('delete removes file via storage then calls repo delete', function () {
    Storage::fake('public');
    $qr = new \App\Models\qrCode();
    $qr->forceFill(['id' => 5, 'qr' => 'qrcodes/test.png']);

    $service = makeQrSvc(
        function (MockInterface $m) use ($qr) {
            $m->shouldReceive('findOrFail')->once()->with(5)->andReturn($qr);
            $m->shouldReceive('delete')->once()->with($qr);
        },
        function (MockInterface $m) {
            $m->shouldReceive('delete')->once()->with('qrcodes/test.png');
        }
    );

    $service->delete(5);
});

// ── create ────────────────────────────────────────────────────────────────────

it('create stores qr image and creates repo record', function () {
    Storage::fake('public');

    $qr = (new \App\Models\qrCode())->forceFill(['id' => 1, 'qr' => 'qrcodes/abc.png', 'data' => 'Place A']);

    $service = makeQrSvc(function (MockInterface $m) use ($qr) {
        $m->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn ($arg) =>
                str_ends_with($arg['qr'], '.png') && $arg['data'] === 'Place A'
            ))
            ->andReturn($qr);
    });

    $result = $service->create('Place A', null, 1);

    expect($result->id)->toBe(1);
});

// ── update ────────────────────────────────────────────────────────────────────

it('update overwrites qr file and calls repo update', function () {
    Storage::fake('public');
    $qr = new \App\Models\qrCode();
    $qr->forceFill(['id' => 7, 'qr' => 'qrcodes/old.png', 'data' => 'old']);

    $service = makeQrSvc(function (MockInterface $m) use ($qr) {
        $m->shouldReceive('findOrFail')->once()->with(7)->andReturn($qr);
        $m->shouldReceive('update')
            ->once()
            ->with($qr, Mockery::on(fn ($arg) => $arg['data'] === 'New Place'))
            ->andReturn($qr);
    });

    $service->update(7, 'New Place');
});
