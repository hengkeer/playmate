<?php

namespace App\Filament\Resources\Sport\Tables;

use App\Models\Sport;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class SportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('venues_count')->counts('venues')->label('Venues'),
                TextColumn::make('events_count')->counts('events')->label('Events'),
                TextColumn::make('created_at')->dateTime('M j, Y'),
            ])
            ->defaultSort('name');
    }
}