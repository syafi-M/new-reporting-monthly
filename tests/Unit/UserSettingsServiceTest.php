<?php

use App\Models\UserSettings;
use App\Repositories\Contracts\UserSettingsRepositoryInterface;
use App\Services\Settings\UserSettingsService;
use Mockery\MockInterface;
use Tests\TestCase;

uses(TestCase::class);
afterEach(fn () => Mockery::close());

function makeUserSettingsService(callable $setup): UserSettingsService
{
    return new UserSettingsService(
        Mockery::mock(UserSettingsRepositoryInterface::class, $setup)
    );
}

// ── getByUser ────────────────────────────────────────────────────────────────

it('returns settings when found', function () {
    $settings = new UserSettings();
    $settings->forceFill(['user_id' => 1, 'data_theme' => ['color' => 'blue']]);

    $service = makeUserSettingsService(function (MockInterface $m) use ($settings) {
        $m->shouldReceive('findByUserId')->once()->with(1)->andReturn($settings);
    });

    expect($service->getByUser(1))->toBe($settings);
});

it('returns null when settings not found', function () {
    $service = makeUserSettingsService(function (MockInterface $m) {
        $m->shouldReceive('findByUserId')->once()->with(99)->andReturn(null);
    });

    expect($service->getByUser(99))->toBeNull();
});

// ── storeTheme ───────────────────────────────────────────────────────────────

it('delegates upsert to repository and returns result', function () {
    $theme = ['sidebar' => 'dark', 'primary' => '#333'];
    $settings = new UserSettings();
    $settings->forceFill(['user_id' => 5, 'data_theme' => $theme]);

    $service = makeUserSettingsService(function (MockInterface $m) use ($settings, $theme) {
        $m->shouldReceive('upsertTheme')->once()->with(5, $theme)->andReturn($settings);
    });

    expect($service->storeTheme(5, $theme))->toBe($settings);
});

it('passes empty theme array to repository', function () {
    $settings = new UserSettings();
    $settings->forceFill(['user_id' => 3, 'data_theme' => []]);

    $service = makeUserSettingsService(function (MockInterface $m) use ($settings) {
        $m->shouldReceive('upsertTheme')->once()->with(3, [])->andReturn($settings);
    });

    expect($service->storeTheme(3, []))->toBe($settings);
});
