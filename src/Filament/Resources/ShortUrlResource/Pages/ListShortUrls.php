<?php

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListShortUrls extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ShortUrlResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return ShortUrlResource::getWidgets();
    }
}
