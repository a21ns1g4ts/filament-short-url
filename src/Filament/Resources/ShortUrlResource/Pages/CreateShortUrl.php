<?php

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use AshAllenDesign\ShortURL\Classes\Builder;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CreateShortUrl extends CreateRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $shortUrl = app(Builder::class)->destinationUrl($data['destination_url'])
            ->when($data['activated_at'], fn (Builder $builder) => $builder->activateAt(Carbon::parse($data['activated_at'])))
            ->when($data['deactivated_at'], fn (Builder $builder) => $builder->deactivateAt(Carbon::parse($data['deactivated_at'])))
            ->make();

        return $shortUrl;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('index')
                ->label('List')
                ->url(ListShortUrls::getUrl()),
        ];
    }
}
