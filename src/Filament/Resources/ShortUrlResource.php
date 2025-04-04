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
                Forms\Components\Section::make('Informações do Link')
                    ->description('Crie e gerencie seus links curtos')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('destination_url')
                            ->required()
                            ->live()
                            ->maxLength(255)
                            ->url()
                            ->placeholder('https://exemplo.com/url-longa')
                            ->columnSpan(['lg' => 3, 'xs' => 6])
                            ->helperText('A URL original que será encurtada'),

                        Forms\Components\TextInput::make('default_short_url')
                            ->readOnly()
                            ->maxLength(255)
                            ->columnSpan(['xl' => 2, 'xs' => 6])
                            ->suffixIcon('heroicon-o-clipboard')
                            ->helperText('Clique para copiar'),

                        Forms\Components\TextInput::make('url_key')
                            ->readOnly()
                            ->maxLength(255)
                            ->columnSpan(['xl' => 1, 'xs' => 6])
                            ->helperText('Identificador único'),
                    ])
                    ->columns(6)
                    ->columnSpanFull(),

                Forms\Components\Section::make('Configurações de Rastreamento')
                    ->collapsible()
                    ->schema([
                        Forms\Components\ToggleButtons::make('tracking_options')
                            ->inline()
                            ->options([
                                'basic' => 'Básico',
                                'advanced' => 'Avançado',
                                'none' => 'Nenhum',
                            ])
                            ->default('advanced')
                            ->grouped()
                            ->live(),

                        Forms\Components\Fieldset::make('Opções Avançadas')
                            ->hidden(fn (Forms\Get $get) => $get('tracking_options') === 'none')
                            ->schema([
                                Toggle::make('track_visits')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Registrar acessos ao link'),

                                // Agrupar toggles relacionados
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Toggle::make('track_ip_address')
                                            ->default(true)
                                            ->inline(false),
                                        Toggle::make('track_browser')
                                            ->default(true)
                                            ->inline(false),
                                        Toggle::make('track_device_type')
                                            ->default(true)
                                            ->inline(false),
                                    ]),
                            ]),
                    ]),

                Forms\Components\Section::make('Ativação')
                    ->collapsible()
                    ->schema([
                        Forms\Components\DateTimePicker::make('activated_at')
                            ->helperText('Quando o link estará ativo'),
                        Forms\Components\DateTimePicker::make('deactivated_at')
                            ->helperText('Quando o link será desativado'),
                    ])
                    ->columns(2),
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
