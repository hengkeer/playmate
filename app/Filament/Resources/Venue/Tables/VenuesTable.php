<?php

namespace App\Filament\Resources\Venue\Tables;

use App\Models\Venue;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class VenuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')->label('Photo')->disk('public')->square(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sport.name')->label('Sport')->badge(),
                TextColumn::make('area')->sortable(),
                TextColumn::make('open_hours')->label('Hours'),
                TextColumn::make('price_estimate')->money('IDR')->label('Price/hr'),
                BooleanColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('name');
    }
}