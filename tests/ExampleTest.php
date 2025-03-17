<?php

namespace Tests\Filament\Resources;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use AshAllenDesign\ShortURL\Models\ShortURL;

beforeEach(function () {
    $this->shortUrl = ShortURL::factory()->create();
});

it('has correct navigation items', function () {
    $pages = ShortUrlResource::getPages();

    expect($pages)->toHaveKeys([
        'index',
        'create',
        'view',
        'edit',
        'visits',
    ]);
});

it('has correct widgets', function () {
    $widgets = ShortUrlResource::getWidgets();

    expect($widgets)->toContain(\A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Widgets\ShortUrlStats::class);
});

it('respects tenant scope configuration', function () {
    config(['filament-short-url.tenant_scope' => true]);
    expect(ShortUrlResource::isScopedToTenant())->toBeTrue();

    config(['filament-short-url.tenant_scope' => false]);
    expect(ShortUrlResource::isScopedToTenant())->toBeFalse();
});
