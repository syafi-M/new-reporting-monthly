<?php

use App\Services\Shared\PeriodService;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

afterEach(fn () => Carbon::setTestNow());

// ── basic resolution ─────────────────────────────────────────────────────────

it('resolves explicit month and year', function () {
    $r = (new PeriodService())->monthRange(3, 2025);

    expect($r['month'])->toBe(3)
        ->and($r['year'])->toBe(2025)
        ->and($r['start_at']->toDateString())->toBe('2025-03-01')
        ->and($r['end_at']->toDateString())->toBe('2025-03-31');
});

it('falls back to current month/year when no args', function () {
    Carbon::setTestNow(Carbon::create(2026, 6, 15));

    $r = (new PeriodService())->monthRange();

    expect($r['month'])->toBe(6)
        ->and($r['year'])->toBe(2026)
        ->and($r['start_at']->toDateString())->toBe('2026-06-01')
        ->and($r['end_at']->toDateString())->toBe('2026-06-30');
});

// ── time boundaries ──────────────────────────────────────────────────────────

it('start_at is start-of-day', function () {
    $r = (new PeriodService())->monthRange(1, 2026);

    expect($r['start_at']->hour)->toBe(0)
        ->and($r['start_at']->minute)->toBe(0)
        ->and($r['start_at']->second)->toBe(0);
});

it('end_at is end-of-day', function () {
    $r = (new PeriodService())->monthRange(1, 2026);

    expect($r['end_at']->hour)->toBe(23)
        ->and($r['end_at']->minute)->toBe(59)
        ->and($r['end_at']->second)->toBe(59);
});

// ── edge: february ───────────────────────────────────────────────────────────

it('feb ends on 29 for leap year', function () {
    expect((new PeriodService())->monthRange(2, 2024)['end_at']->toDateString())
        ->toBe('2024-02-29');
});

it('feb ends on 28 for non-leap year', function () {
    expect((new PeriodService())->monthRange(2, 2025)['end_at']->toDateString())
        ->toBe('2025-02-28');
});

// ── edge: december ───────────────────────────────────────────────────────────

it('december ends on 31', function () {
    expect((new PeriodService())->monthRange(12, 2025)['end_at']->toDateString())
        ->toBe('2025-12-31');
});
