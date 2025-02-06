<?php

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use AshAllenDesign\ShortURL\Classes\Builder;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CreateShortUrl extends CreateRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $shortUrl = app(Builder::class)->destinationUrl($data['destination_url'])
            ->when($data['activated_at'], fn (Builder $builder) => $builder->activateAt(Carbon::parse($data['activated_at'])))
            ->when($data['deactivated_at'], fn (Builder $builder) => $builder->deactivateAt(Carbon::parse($data['deactivated_at'])))
            ->beforeCreate(function (ShortURL $model): void {
                if (Schema::hasColumn('short_urls', 'company_id')) {
                    $model->company_id = auth()->user()->current_company_id;
                }
            })
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
