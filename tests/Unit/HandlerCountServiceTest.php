<?php

use App\Repositories\Contracts\MonitoringRepositoryInterface;
use App\Services\Monitoring\HandlerCountService;
use App\Services\Shared\PeriodService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

function makeHandlerCountService(callable $repoSetup): HandlerCountService
{
    return new HandlerCountService(
        Mockery::mock(MonitoringRepositoryInterface::class, $repoSetup),
        new PeriodService()
    );
}

// ── indexData ─────────────────────────────────────────────────────────────────

it('indexData returns clients key', function () {
    $service = makeHandlerCountService(function (MockInterface $m) {
        $m->shouldReceive('getClientsLite')->once()->andReturn(collect([1, 2]));
    });

    $result = $service->indexData();

    expect($result)->toHaveKey('clients')
        ->and($result['clients'])->toHaveCount(2);
});

// ── show ──────────────────────────────────────────────────────────────────────

it('show returns fixed, user, client keys', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 1));

    $user = (object) ['id' => 1, 'kerjasama' => (object) ['client_id' => 10]];

    $service = makeHandlerCountService(function (MockInterface $m) use ($user) {
        $m->shouldReceive('getUserBasic')->once()->with(1)->andReturn($user);
        $m->shouldReceive('getFixedByUserBetween')
            ->once()
            ->with(1, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
            ->andReturn(collect());
        $m->shouldReceive('getMitraByClientId')->once()->with(10)->andReturn((object) ['id' => 10]);
    });

    $result = $service->show(1, 6, 2026);

    expect($result)->toHaveKeys(['fixed', 'user', 'client']);

    Carbon::setTestNow();
});

it('show passes correct period boundaries to repo', function () {
    Carbon::setTestNow(Carbon::create(2026, 3, 15));

    $user = (object) ['id' => 2, 'kerjasama' => (object) ['client_id' => 5]];

    $service = makeHandlerCountService(function (MockInterface $m) use ($user) {
        $m->shouldReceive('getUserBasic')->andReturn($user);
        $m->shouldReceive('getFixedByUserBetween')
            ->once()
            ->with(
                2,
                Mockery::on(fn ($d) => $d->toDateString() === '2026-03-01'),
                Mockery::on(fn ($d) => $d->toDateString() === '2026-03-31'),
            )
            ->andReturn(collect());
        $m->shouldReceive('getMitraByClientId')->andReturn(null);
    });

    $service->show(2, 3, 2026);

    Carbon::setTestNow();
});
