<?php

declare(strict_types=1);

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewShortUrl extends ViewRecord
{
    protected static string $resource = ShortUrlResource::class;

    public function getTitle(): string | Htmlable
    {
        /* @var \AshAllenDesign\ShortURL\Models\ShortURL $record */
        $record = $this->getRecord();

        return $record->default_short_url ?? 'Short URL';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
