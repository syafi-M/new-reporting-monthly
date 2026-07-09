<?php

use App\Repositories\Contracts\MonitoringRepositoryInterface;
use App\Services\Monitoring\DashboardService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

function makeDashboardRepo(callable $setup): MonitoringRepositoryInterface
{
    return Mockery::mock(MonitoringRepositoryInterface::class, $setup);
}

// ── getAdminDashboardData ────────────────────────────────────────────────────

it('returns all required keys for admin dashboard', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 1));

    $repo = makeDashboardRepo(function (MockInterface $m) {
        $m->shouldReceive('getFixedGroupedSummary')->once()->andReturn(collect());
        $m->shouldReceive('countUploadsForMonth')->twice()->andReturn(10);
        $m->shouldReceive('countSessionsForMonth')->twice()->andReturn(5);
        $m->shouldReceive('latestActivities')->once()->andReturn(collect());
    });

    $result = (new DashboardService($repo))->getAdminDashboardData();

    expect($result)->toHaveKeys([
        'totalThisMonth', 'totalLastMonth', 'growthDirection', 'growthAbs',
        'result', 'activities', 'current', 'percentage', 'isUp',
    ]);

    Carbon::setTestNow();
});

it('growthDirection is up when this month > last month', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 1));

    $repo = makeDashboardRepo(function (MockInterface $m) {
        $m->shouldReceive('getFixedGroupedSummary')->once()->andReturn(collect());
        $m->shouldReceive('countUploadsForMonth')
            ->twice()
            ->andReturn(20, 10); // this month, last month
        $m->shouldReceive('countSessionsForMonth')->twice()->andReturn(5);
        $m->shouldReceive('latestActivities')->once()->andReturn(collect());
    });

    $result = (new DashboardService($repo))->getAdminDashboardData();

    expect($result['growthDirection'])->toBe('up');

    Carbon::setTestNow();
});

it('growthDirection is down when this month < last month', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 1));

    $repo = makeDashboardRepo(function (MockInterface $m) {
        $m->shouldReceive('getFixedGroupedSummary')->once()->andReturn(collect());
        $m->shouldReceive('countUploadsForMonth')
            ->twice()
            ->andReturn(5, 20); // this month, last month
        $m->shouldReceive('countSessionsForMonth')->twice()->andReturn(5);
        $m->shouldReceive('latestActivities')->once()->andReturn(collect());
    });

    $result = (new DashboardService($repo))->getAdminDashboardData();

    expect($result['growthDirection'])->toBe('down');

    Carbon::setTestNow();
});

it('growth is 100 percent when last month is zero', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 1));

    $repo = makeDashboardRepo(function (MockInterface $m) {
        $m->shouldReceive('getFixedGroupedSummary')->once()->andReturn(collect());
        $m->shouldReceive('countUploadsForMonth')
            ->twice()
            ->andReturn(5, 0);
        $m->shouldReceive('countSessionsForMonth')->twice()->andReturn(0);
        $m->shouldReceive('latestActivities')->once()->andReturn(collect());
    });

    $result = (new DashboardService($repo))->getAdminDashboardData();

    expect($result['growthAbs'])->toBe(100)
        ->and($result['percentage'])->toBe(100.0);

    Carbon::setTestNow();
});

it('percentage capped at 100 for uploads exceeding quota', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 1));

    // row with 50 uploads → 50/11 > 100%, should be capped to 100
    $row = (object) [
        'clients_id' => 1,
        'total' => 50,
        'user' => (object) [
            'divisi' => (object) [
                'jabatan' => (object) ['name_jabatan' => 'Leader'],
            ],
        ],
    ];

    $repo = makeDashboardRepo(function (MockInterface $m) use ($row) {
        $m->shouldReceive('getFixedGroupedSummary')->once()->andReturn(collect([$row]));
        $m->shouldReceive('getClientNameById')->once()->with(1)->andReturn('Client A');
        $m->shouldReceive('countUploadsForMonth')->twice()->andReturn(0);
        $m->shouldReceive('countSessionsForMonth')->twice()->andReturn(0);
        $m->shouldReceive('latestActivities')->once()->andReturn(collect());
    });

    $result = (new DashboardService($repo))->getAdminDashboardData();

    expect($result['result'][0]['percentage'])->toBe(100);

    Carbon::setTestNow();
});

it('uses Unknown when jabatan chain is missing', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 1));

    $row = (object) [
        'clients_id' => 2,
        'total' => 3,
        'user' => (object) ['divisi' => null],
    ];

    $repo = makeDashboardRepo(function (MockInterface $m) use ($row) {
        $m->shouldReceive('getFixedGroupedSummary')->once()->andReturn(collect([$row]));
        $m->shouldReceive('getClientNameById')->once()->with(2)->andReturn('Client B');
        $m->shouldReceive('countUploadsForMonth')->twice()->andReturn(0);
        $m->shouldReceive('countSessionsForMonth')->twice()->andReturn(0);
        $m->shouldReceive('latestActivities')->once()->andReturn(collect());
    });

    $result = (new DashboardService($repo))->getAdminDashboardData();

    expect($result['result'][0]['jabatan'])->toBe('Unknown');

    Carbon::setTestNow();
});

// ── getUserDashboardData ─────────────────────────────────────────────────────

it('returns totalImageCount and chart for user dashboard', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 15));

    $performance = collect([
        (object) ['month' => 1, 'total' => 2],
        (object) ['month' => 6, 'total' => 5],
    ]);

    $repo = makeDashboardRepo(function (MockInterface $m) use ($performance) {
        $m->shouldReceive('countFixedByClientMonth')->once()->andReturn(7);
        $m->shouldReceive('getUserPerformanceByMonth')->once()->andReturn($performance);
    });

    $result = (new DashboardService($repo))->getUserDashboardData(1, 10, now());

    expect($result['totalImageCount'])->toBe(7)
        ->and($result['chart'])->toHaveKeys(['months', 'totals', 'year'])
        ->and($result['chart']['totals'])->toHaveCount(12);

    Carbon::setTestNow();
});

it('chart fills zero for months with no data', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 15));

    $repo = makeDashboardRepo(function (MockInterface $m) {
        $m->shouldReceive('countFixedByClientMonth')->once()->andReturn(0);
        $m->shouldReceive('getUserPerformanceByMonth')->once()->andReturn(collect());
    });

    $result = (new DashboardService($repo))->getUserDashboardData(1, 10, now());

    // chart is ['label' => total, ...] keyed by month name
    expect($result['chart'])->toHaveKeys(['months', 'totals', 'year'])
        ->and(array_sum($result['chart']['totals']))->toBe(0);

    Carbon::setTestNow();
});
