<?php

declare(strict_types=1);

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
        $builder = app(Builder::class)->destinationUrl($data['destination_url']);

        if (! empty($data['url_key'])) {
            $builder->urlKey((string) $data['url_key']);
        }

        if (! empty($data['activated_at'])) {
            $builder->activateAt(Carbon::parse($data['activated_at']));
        }

        if (! empty($data['deactivated_at'])) {
            $builder->deactivateAt(Carbon::parse($data['deactivated_at']));
        }

        if (array_key_exists('track_visits', $data)) {
            $builder->trackVisits((bool) $data['track_visits']);
        }

        if (array_key_exists('track_ip_address', $data)) {
            $builder->trackIPAddress((bool) $data['track_ip_address']);
        }

        if (array_key_exists('track_browser', $data)) {
            $builder->trackBrowser((bool) $data['track_browser']);
        }

        if (array_key_exists('track_device_type', $data)) {
            $builder->trackDeviceType((bool) $data['track_device_type']);
        }

        if (array_key_exists('track_referer_url', $data)) {
            $builder->trackRefererURL((bool) $data['track_referer_url']);
        }

        if (array_key_exists('single_use', $data)) {
            $builder->singleUse((bool) $data['single_use']);
        }

        if (array_key_exists('forward_query_params', $data)) {
            $builder->forwardQueryParams((bool) $data['forward_query_params']);
        }

        return $builder
            ->beforeCreate(function (ShortURL $model): void {
                if (Schema::hasColumn('short_urls', 'company_id') && auth()->check()) {
                    $user = auth()->user();
                    $model->company_id = $user->current_company_id ?? $user->company_id ?? null;
                }
            })
            ->make();
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
