<?php

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Widgets;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages\ListShortUrls;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShortUrlStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListShortUrls::class;
    }

    protected function getStats(): array
    {
        $total = $this->getPageTableQuery()->count();
        $totalVisits = $this->getPageTableQuery()->join('short_url_visits', 'short_urls.id', '=', 'short_url_visits.short_url_id')->count();
        $averageVisits = number_format($totalVisits / max($total, 1), 2);

        return [
            Stat::make('Total', $total),
            Stat::make('Total Visits', $totalVisits),
            Stat::make('Average Visits', $averageVisits),
        ];
    }
}
