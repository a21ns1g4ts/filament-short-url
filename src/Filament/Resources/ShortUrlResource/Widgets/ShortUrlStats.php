<?php

declare(strict_types=1);

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Widgets;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages\ListShortUrls;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShortUrlStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListShortUrls::class;
    }

    protected function getStats(): array
    {
        $query = $this->getPageTableQuery();

        $total = (clone $query)->count();
        $totalVisits = (clone $query)
            ->join('short_url_visits', 'short_urls.id', '=', 'short_url_visits.short_url_id')
            ->count();

        $averageVisits = number_format($totalVisits / max($total, 1), 2);

        return [
            Stat::make('Total Links', (string) $total)
                ->description('Total short URLs')
                ->icon('heroicon-o-link'),
            Stat::make('Total Visits', (string) $totalVisits)
                ->description('Recorded link visits')
                ->icon('heroicon-o-chart-bar')
                ->color('success'),
            Stat::make('Average Visits', (string) $averageVisits)
                ->description('Visits per link')
                ->icon('heroicon-o-calculator')
                ->color('info'),
        ];
    }
}
