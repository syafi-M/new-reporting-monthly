<?php

use App\Services\FindingService;
use App\Models\Finding;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

// ── getIndexData ─────────────────────────────────────────────────────────────

it('getIndexData returns findings and remainingQuota keys', function () {
    $alias = Mockery::mock('alias:App\Models\Finding');
    $query = Mockery::mock();

    $alias->shouldReceive('with')->once()->with('user')->andReturn($query);
    $query->shouldReceive('latest->get')->once()->andReturn(collect([]));

    $service = new FindingService();
    $result = $service->getIndexData();

    expect($result)->toHaveKeys(['findings', 'remainingQuota']);
});

it('remainingQuota reflects request param', function () {
    request()->merge(['ruangan' => 'Area X']);

    $alias = Mockery::mock('alias:App\Models\Finding');
    $query = Mockery::mock();

    $alias->shouldReceive('with')->andReturn($query);
    $query->shouldReceive('latest->get')->andReturn(collect([]));
    $alias->shouldReceive('where->whereDate->count')->andReturn(2);

    $service = new FindingService();
    $result = $service->getIndexData();

    expect($result['remainingQuota'])->toBeGreaterThanOrEqual(0);
});
