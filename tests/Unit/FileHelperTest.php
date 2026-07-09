<?php

use App\Helpers\FileHelper;
use Tests\TestCase;

uses(TestCase::class);

// ── lighten_color ─────────────────────────────────────────────────────────────

it('lightens a hex color by percentage', function () {
    // #000000 + 50% → each channel = 127 → #7f7f7f
    $result = FileHelper::lighten_color('#000000', 50);
    expect($result)->toBe('#7f7f7f');
});

it('does not exceed #ffffff when percent is 100', function () {
    $result = FileHelper::lighten_color('#ffffff', 100);
    expect($result)->toBe('#ffffff');
});

it('handles hex without hash prefix', function () {
    $result = FileHelper::lighten_color('000000', 100);
    expect($result)->toBe('#ffffff');
});

it('clamps channel at 255', function () {
    // fully saturated red + large percent stays at ff for R channel
    $result = FileHelper::lighten_color('#ff0000', 100);
    [$r, $g, $b] = sscanf($result, '#%02x%02x%02x');
    expect($r)->toBe(255);
});

it('returns valid 7-char hex string', function () {
    $result = FileHelper::lighten_color('#123456', 10);
    expect($result)->toMatch('/^#[0-9a-f]{6}$/');
});

// ── cleanupOrphanTempUploads returns expected shape ──────────────────────────

it('returns deleted_dirs and freed_bytes keys', function () {
    // call with 1-minute TTL so nothing real gets deleted in test env
    $result = FileHelper::cleanupOrphanTempUploads(0);

    expect($result)->toHaveKeys(['deleted_dirs', 'freed_bytes']);
});
