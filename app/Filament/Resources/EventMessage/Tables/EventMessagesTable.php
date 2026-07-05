<?php

namespace App\Filament\Resources\EventMessage\Tables;

use App\Models\EventMessage;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class EventMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('event.title')->label('Event')->searchable(),
                TextColumn::make('user.name')->label('User')->searchable(),
                TextColumn::make('type'),
                TextColumn::make('content')->limit(80),
                TextColumn::make('created_at')->dateTime('M j, Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}