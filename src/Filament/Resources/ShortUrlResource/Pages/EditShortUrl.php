<?php

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditShortUrl extends EditRecord
{
    protected static string $resource = ShortUrlResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var ShorUrl */
        $record = $this->getRecord();

        return $record->default_short_url;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('create')
                ->label('New')
                ->url(CreateShortUrl::getUrl()),
        ];
    }
}
