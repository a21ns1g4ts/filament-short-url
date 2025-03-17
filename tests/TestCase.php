<?php

namespace A21ns1g4ts\FilamentShortUrl\Tests;

use A21ns1g4ts\FilamentShortUrl\FilamentShortUrlServiceProvider;
use AshAllenDesign\ShortURL\Models\Factories\ShortURLFactory;
use AshAllenDesign\ShortURL\Models\Factories\ShortURLVisitFactory;
use AshAllenDesign\ShortURL\Models\ShortURL;
use AshAllenDesign\ShortURL\Models\ShortURLVisit;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Auth\User;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::forceCreate([
            'name' => 'Test User',
            'email' => 'q5oqW@example.com',
        ]));

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'A21ns1g4ts\\FilamentShortUrl\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentShortUrlServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('filament-short-url.tenant_scope', false);
        config()->set('short-url.factories', [
            ShortURL::class => ShortURLFactory::class,
            ShortURLVisit::class => ShortURLVisitFactory::class,
        ]);

        config()->set('short-url.connection', 'testing');

        $migration = include __DIR__ . '/database/migrations/create_short_urls_table.php';
        $migration->up();
        $migration = include __DIR__ . '/database/migrations/create_users_table.php';
        $migration->up();
    }
}
