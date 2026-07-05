<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('location')->searchable(),
                TextColumn::make('host.name')->label('Host'),
                TextColumn::make('start_time')->dateTime('M j, Y H:i')->sortable(),
                TextColumn::make('end_time')->dateTime('H:i'),
                TextColumn::make('participants_count')
                    ->counts('participants')
                    ->label('Participants'),
            ])
            ->defaultSort('start_time', 'desc');
    }
}