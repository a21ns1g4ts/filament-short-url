<?php

namespace A21ns1g4ts\FilamentShortUrl;

use A21ns1g4ts\FilamentShortUrl\Commands\FilamentShortUrlCommand;
use A21ns1g4ts\FilamentShortUrl\Testing\TestsFilamentShortUrl;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentShortUrlServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-short-url';

    public static string $viewNamespace = 'filament-short-url';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('a21ns1g4ts/filament-short-url');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        // if (file_exists($package->basePath('/../database/migrations'))) {
        //     $package->hasMigrations($this->getMigrations());
        // }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        // if (file_exists($package->basePath('/../resources/views'))) {
        //     $package->hasViews(static::$viewNamespace);
        // }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        // FilamentAsset::register(
        //     $this->getAssets(),
        //     $this->getAssetPackageName()
        // );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-short-url/{$file->getFilename()}"),
                ], 'filament-short-url-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentShortUrl);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'a21ns1g4ts/filament-short-url';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-short-url', __DIR__ . '/../resources/dist/components/filament-short-url.js'),
            Css::make('filament-short-url-styles', __DIR__ . '/../resources/dist/filament-short-url.css'),
            Js::make('filament-short-url-scripts', __DIR__ . '/../resources/dist/filament-short-url.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentShortUrlCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-short-url_table',
        ];
    }
}
