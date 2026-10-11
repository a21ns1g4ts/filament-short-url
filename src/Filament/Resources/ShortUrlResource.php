<?php

declare(strict_types=1);

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;
use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Widgets\ShortUrlStats;
use A21ns1g4ts\FilamentShortUrl\FilamentShortUrlPlugin;
use AshAllenDesign\ShortURL\Models\ShortURL;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ShortUrlResource extends Resource
{
    protected static ?string $model = ShortURL::class;

    protected static ?string $modelLabel = 'Short URL';

    protected static ?string $pluralModelLabel = 'Short URLs';

    protected static ?string $navigationBadgeColor = 'primary';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-link';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationIcon(): string | \BackedEnum | null
    {
        try {
            $icon = FilamentShortUrlPlugin::get()->getNavigationIcon();
            if ($icon !== null) {
                return $icon;
            }
        } catch (\Throwable) {
        }

        return static::$navigationIcon;
    }

    public static function getNavigationGroup(): ?string
    {
        try {
            $group = FilamentShortUrlPlugin::get()->getNavigationGroup();
            if ($group !== null) {
                return $group;
            }
        } catch (\Throwable) {
        }

        return config('filament-short-url.navigation_group');
    }

    public static function getNavigationSort(): ?int
    {
        try {
            $sort = FilamentShortUrlPlugin::get()->getNavigationSort();
            if ($sort !== null) {
                return $sort;
            }
        } catch (\Throwable) {
        }

        return config('filament-short-url.navigation_sort');
    }

    public static function getNavigationLabel(): string
    {
        try {
            $label = FilamentShortUrlPlugin::get()->getNavigationLabel();
            if ($label !== null) {
                return $label;
            }
        } catch (\Throwable) {
        }

        return (string) (config('filament-short-url.navigation_label') ?? static::$pluralModelLabel);
    }

    public static function isScopedToTenant(): bool
    {
        try {
            $scoped = FilamentShortUrlPlugin::get()->getIsScopedToTenant();
            if ($scoped !== null) {
                return $scoped;
            }
        } catch (\Throwable) {
        }

        return (bool) config('filament-short-url.tenant_scope', false);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('URL Information')
                    ->description('Create and manage your short links')
                    ->schema([
                        Forms\Components\TextInput::make('destination_url')
                            ->label('Destination URL')
                            ->placeholder('https://example.com/long-url')
                            ->helperText('The original target URL')
                            ->required()
                            ->url()
                            ->maxLength(2048)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('url_key')
                            ->label('URL Key')
                            ->placeholder('custom-code')
                            ->helperText('Optional unique short code (alphanumeric, dashes, underscores)')
                            ->nullable()
                            ->alphaDash()
                            ->maxLength(255)
                            ->unique(table: 'short_urls', column: 'url_key', ignoreRecord: true),

                        Forms\Components\TextInput::make('default_short_url')
                            ->label('Short URL')
                            ->helperText('Generated short URL')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn(['view', 'edit']),
                    ])
                    ->columns(2),

                Section::make('Tracking & Behavior')
                    ->schema([
                        Toggle::make('track_visits')
                            ->label('Track Visits')
                            ->helperText('Record access count and stats')
                            ->default(true),

                        Toggle::make('track_ip_address')
                            ->label('Track IP Address')
                            ->default(true),

                        Toggle::make('track_browser')
                            ->label('Track Browser & OS')
                            ->default(true),

                        Toggle::make('track_device_type')
                            ->label('Track Device Type')
                            ->default(true),

                        Toggle::make('track_referer_url')
                            ->label('Track Referrer URL')
                            ->default(true),

                        Toggle::make('single_use')
                            ->label('Single Use')
                            ->helperText('Deactivates automatically after the first access')
                            ->default(false),

                        Toggle::make('forward_query_params')
                            ->label('Forward Query Parameters')
                            ->helperText('Pass incoming query parameters to the destination URL')
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make('Activation Period')
                    ->schema([
                        DateTimePicker::make('activated_at')
                            ->label('Activation Date')
                            ->helperText('When the link becomes active (leave empty for immediate)')
                            ->native(false),

                        DateTimePicker::make('deactivated_at')
                            ->label('Expiration Date')
                            ->helperText('When the link will deactivate (leave empty for never)')
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('destination_url')
                    ->label('Destination URL')
                    ->limit(45)
                    ->copyable()
                    ->color('primary')
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('default_short_url')
                    ->label('Short URL')
                    ->copyable()
                    ->color('success')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url_key')
                    ->label('Key')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visits_count')
                    ->label('Visits')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total Visits'))
                    ->counts('visits'),
                Tables\Columns\IconColumn::make('single_use')
                    ->label('Single Use')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('activated_at')
                    ->label('Activated At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deactivated_at')
                    ->label('Deactivated At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\Filter::make('activated_period')
                    ->form([
                        Forms\Components\DatePicker::make('activated_from')
                            ->label('Activated From')
                            ->native(false),
                        Forms\Components\DatePicker::make('activated_until')
                            ->label('Activated Until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['activated_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('activated_at', '>=', $date),
                            )
                            ->when(
                                $data['activated_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('activated_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['activated_from'] ?? null) {
                            $indicators['activated_from'] = 'Activated from ' . Carbon::parse($data['activated_from'])->toFormattedDateString();
                        }
                        if ($data['activated_until'] ?? null) {
                            $indicators['activated_until'] = 'Activated until ' . Carbon::parse($data['activated_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->button()
                    ->color('gray')
                    ->size('sm')
                    ->label('Actions'),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('General Information')
                    ->schema([
                        TextEntry::make('destination_url')
                            ->label('Destination URL')
                            ->copyable()
                            ->color('primary')
                            ->columnSpanFull(),
                        TextEntry::make('default_short_url')
                            ->label('Short URL')
                            ->copyable()
                            ->color('success'),
                        TextEntry::make('url_key')
                            ->label('Key')
                            ->badge(),
                        ImageEntry::make('qr_code')
                            ->label('QR Code')
                            ->state(fn ($record) => self::getQrCode($record?->default_short_url)),
                    ])
                    ->columns(2),

                Section::make('Dates & Status')
                    ->schema([
                        TextEntry::make('activated_at')
                            ->label('Activated At')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('deactivated_at')
                            ->label('Deactivated At')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(4),

                Section::make('Tracking Capabilities')
                    ->schema([
                        IconEntry::make('single_use')->label('Single Use')->boolean(),
                        IconEntry::make('forward_query_params')->label('Forward Query Params')->boolean(),
                        IconEntry::make('track_visits')->label('Track Visits')->boolean(),
                        IconEntry::make('track_ip_address')->label('Track IP')->boolean(),
                        IconEntry::make('track_operating_system')->label('Track OS')->boolean(),
                        IconEntry::make('track_operating_system_version')->label('Track OS Version')->boolean(),
                        IconEntry::make('track_browser')->label('Track Browser')->boolean(),
                        IconEntry::make('track_browser_version')->label('Track Browser Version')->boolean(),
                        IconEntry::make('track_referer_url')->label('Track Referrer')->boolean(),
                        IconEntry::make('track_device_type')->label('Track Device')->boolean(),
                    ])
                    ->columns(5),
            ]);
    }

    public static function getQrCode(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        try {
            $svg = (new Writer(
                new ImageRenderer(
                    new RendererStyle(160, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                    new SvgImageBackEnd
                )
            ))->writeString($url);

            $trimmed = trim(substr($svg, strpos($svg, "\n") + 1));

            return 'data:image/svg+xml;base64,' . base64_encode($trimmed);
        } catch (\Throwable) {
            return null;
        }
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
