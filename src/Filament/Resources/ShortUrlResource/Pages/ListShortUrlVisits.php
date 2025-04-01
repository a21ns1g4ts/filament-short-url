<?php

namespace A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource\Pages;

use A21ns1g4ts\FilamentShortUrl\Filament\Resources\ShortUrlResource;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ListShortUrlVisits extends ManageRelatedRecords
{
    protected static string $resource = ShortUrlResource::class;

    protected static string $relationship = 'visits';

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public function getTitle(): string | Htmlable
    {
        /* @var \AshAllenDesign\ShortURL\Models\ShortURL $record */
        $record = $this->getRecord();

        return isset($record->default_short_url) ? $record->default_short_url : 'Visits';
    }

    public function getBreadcrumb(): string
    {
        return 'Visits';
    }

    public static function getNavigationLabel(): string
    {
        return 'Visits';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([])
            ->columns(1);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                TextEntry::make('short_url_id'),
                TextEntry::make('ip_address'),
                TextEntry::make('operating_system'),
                TextEntry::make('operating_system_version'),
                TextEntry::make('browser'),
                TextEntry::make('browser_version'),
                TextEntry::make('visited_at'),
                TextEntry::make('referer_url'),
                TextEntry::make('device_type'),
            ])->columns(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visited_at')
                    ->label('Visited At')
                    ->dateTime('M j, Y H:i')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('operating_system')
                    ->label('OS')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('operating_system_version')
                    ->label('OS Version')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('browser')
                    ->label('Browser')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('browser_version')
                    ->label('Browser Version')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('referer_url')
                    ->label('Referer URL')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('device_type')
                    ->label('Device Type')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->groupedBulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id', 'desc');

    }
}
