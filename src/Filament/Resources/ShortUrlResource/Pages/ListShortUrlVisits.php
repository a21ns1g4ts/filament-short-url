<?php

declare(strict_types=1);

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ListShortUrlVisits extends ManageRelatedRecords
{
    protected static string $resource = ShortUrlResource::class;

    protected static string $relationship = 'visits';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    public function getTitle(): string | Htmlable
    {
        /* @var \AshAllenDesign\ShortURL\Models\ShortURL $record */
        $record = $this->getRecord();

        return ($record->default_short_url ?? 'Short URL') . ' - Visits';
    }

    public function getBreadcrumb(): string
    {
        return 'Visits';
    }

    public static function getNavigationLabel(): string
    {
        return 'Visits';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([])
            ->columns(1);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Visit Details')
                    ->schema([
                        TextEntry::make('id')->label('Visit ID'),
                        TextEntry::make('ip_address')->label('IP Address'),
                        TextEntry::make('visited_at')->label('Visited At')->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('device_type')->label('Device Type')->badge(),
                        TextEntry::make('operating_system')->label('Operating System'),
                        TextEntry::make('operating_system_version')->label('OS Version'),
                        TextEntry::make('browser')->label('Browser'),
                        TextEntry::make('browser_version')->label('Browser Version'),
                        TextEntry::make('referer_url')->label('Referrer URL')->columnSpanFull(),
                    ])
                    ->columns(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('visited_at')
                    ->label('Visited At')
                    ->dateTime('d/m/Y H:i:s')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('device_type')
                    ->label('Device')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('operating_system')
                    ->label('OS')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('browser')
                    ->label('Browser')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('referer_url')
                    ->label('Referrer URL')
                    ->limit(40)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->groupedBulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }
}
