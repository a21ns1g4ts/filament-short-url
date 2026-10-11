<?php

namespace A21ns1g4ts\FilamentShortUrl;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentShortUrlPlugin implements Plugin
{
    protected ?string $navigationGroup = null;

    protected ?int $navigationSort = null;

    protected ?string $navigationIcon = null;

    protected ?string $navigationLabel = null;

    protected ?bool $isScopedToTenant = null;

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

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }

    public function navigationIcon(?string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): ?string
    {
        return $this->navigationIcon;
    }

    public function navigationLabel(?string $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function getNavigationLabel(): ?string
    {
        return $this->navigationLabel;
    }

    public function isScopedToTenant(bool $condition = true): static
    {
        $this->isScopedToTenant = $condition;

        return $this;
    }

    public function getIsScopedToTenant(): ?bool
    {
        return $this->isScopedToTenant;
    }
}
