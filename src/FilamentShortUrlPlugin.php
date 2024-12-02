<?php

namespace A21ns1g4ts\FilamentShortUrl;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentShortUrlPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-short-url';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            ShortUrlResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
