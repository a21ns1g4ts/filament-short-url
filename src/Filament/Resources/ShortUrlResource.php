<?php

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;
use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Widgets\ShortUrlStats;
use AshAllenDesign\ShortURL\Models\ShortURL;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ShortUrlResource extends Resource
{
    protected static ?string $modelLabel = 'Short URL';

    protected static ?string $pluralModelLabel = 'Short URLs';

    protected static ?string $navigationBadgeColor = 'primary';

    protected static ?string $model = ShortURL::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function isScopedToTenant(): bool
    {
        return config('filament-short-url.tenant_scope', false);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('URL Information')
                    ->description('Create and manage your short links')
                    ->schema([
                        Forms\Components\TextInput::make('destination_url')
                            ->label('Destination URL')
                            ->placeholder('https://example.com/long-url')
                            ->helperText('The original URL to be shortened'),

                        Forms\Components\TextInput::make('default_short_url')
                            ->label('Short URL')
                            ->helperText('Click to copy'),

                        Forms\Components\TextInput::make('url_key')
                            ->label('URL Key')
                            ->helperText('Unique identifier'),
                    ]),

                Forms\Components\Section::make('Tracking Settings')
                    ->schema([
                        Forms\Components\ToggleButtons::make('tracking_level')
                            ->label('Tracking Level')
                            ->options([
                                'basic' => 'Basic',
                                'advanced' => 'Advanced',
                                'none' => 'None',
                            ]),

                        Forms\Components\Fieldset::make('Advanced Options')
                            ->schema([
                                Toggle::make('track_visits')
                                    ->label('Track Visits')
                                    ->helperText('Record link accesses'),

                                Toggle::make('track_ip_address')
                                    ->label('Track IP Address'),

                                Toggle::make('track_browser')
                                    ->label('Track Browser'),
                            ]),
                    ]),

                Forms\Components\Section::make('Activation Period')
                    ->schema([
                        Forms\Components\DateTimePicker::make('activated_at')
                            ->label('Activation Date')
                            ->helperText('When the link becomes active'),

                        Forms\Components\DateTimePicker::make('deactivated_at')
                            ->label('Expiration Date')
                            ->helperText('When the link will deactivate'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('destination_url')
                    ->copyable()
                    ->limit(50)
                    ->color('primary')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_short_url')
                    ->copyable()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visits_count')
                    ->label('Visits')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Visits'))
                    ->counts('visits'),
                Tables\Columns\TextColumn::make('activated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deactivated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('activated_at'),
                        Forms\Components\DatePicker::make('deactivated_at'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['activated_at'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('activated_at', '>=', $date),
                            )
                            ->when(
                                $data['deactivated_at'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('deactivated_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['deactivated_at'] ?? null) {
                            $indicators['deactivated_at'] = 'Activated from ' . Carbon::parse($data['published_from'])->toFormattedDateString();
                        }
                        if ($data['deactivated_at'] ?? null) {
                            $indicators['deactivated_at'] = 'Deactivated until ' . Carbon::parse($data['deactivated_at'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->action(function () {
                        Notification::make()
                            ->title('You can\'t bulk delete for now! :). This will be implemented in the future.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\Group::make([]),
                        Components\Split::make([
                            Components\Grid::make(10)
                                ->schema([
                                    Components\Group::make([
                                        Components\Group::make([
                                            Components\TextEntry::make('destination_url')
                                                ->limit(50)
                                                ->copyable()
                                                ->color('primary'),
                                            Components\TextEntry::make('default_short_url')
                                                ->copyable()
                                                ->color('primary'),
                                        ]),
                                    ])->columnSpan(4),
                                    Components\Group::make([
                                        Components\Group::make([
                                            Components\ImageEntry::make('destination_url')
                                                ->label('QR Code')
                                                ->state(fn () => self::getQrCode($infolist->getRecord()->default_short_url)),
                                        ]),
                                    ])->columnSpan(2),
                                    Components\Group::make([
                                        Components\Group::make([
                                            Components\TextEntry::make('activated_at'),
                                            Components\TextEntry::make('deactivated_at'),
                                        ]),
                                    ])->columnSpan(2),
                                    Components\Group::make([
                                        Components\Group::make([
                                            Components\TextEntry::make('created_at'),
                                            Components\TextEntry::make('updated_at'),
                                        ]),
                                    ])->columnSpan(2),
                                ]),
                        ])
                            ->from('lg'),
                    ]),
                Components\Section::make()
                    ->schema([
                        Components\Group::make([
                            Components\IconEntry::make('single_use'),
                            Components\IconEntry::make('forward_query_params'),
                            Components\IconEntry::make('track_ip_address'),
                            Components\IconEntry::make('track_operating_system'),
                            Components\IconEntry::make('track_operating_system_version'),
                            Components\IconEntry::make('track_browser'),
                            Components\IconEntry::make('track_browser_version'),
                            Components\IconEntry::make('track_referer_url'),
                            Components\IconEntry::make('track_device_type'),
                        ])->columns(5),
                    ]),
            ]);
    }

    public static function getQrCode(string $url)
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(150, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                new SvgImageBackEnd
            )
        ))->writeString($url);

        $trimmed = trim(substr($svg, strpos($svg, "\n") + 1));

        $url = 'data:image/svg+xml;base64,' . base64_encode($trimmed);

        return $url;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            ShortUrlStats::class,
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewShortUrl::class,
            Pages\EditShortUrl::class,
            Pages\ListShortUrlVisits::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShortUrls::route('/'),
            'create' => Pages\CreateShortUrl::route('/create'),
            'view' => Pages\ViewShortUrl::route('/{record}'),
            'edit' => Pages\EditShortUrl::route('/{record}/edit'),
            'visits' => Pages\ListShortUrlVisits::route('/{record}/visits'),
        ];
    }
}
