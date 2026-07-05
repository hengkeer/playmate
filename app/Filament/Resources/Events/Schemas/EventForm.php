<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('location')
                    ->required()
                    ->maxLength(255),

                Select::make('host_id')
                    ->label('Host')
                    ->relationship('host', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                DateTimePicker::make('start_time')
                    ->required()
                    ->seconds(false),

                DateTimePicker::make('end_time')
                    ->required()
                    ->seconds(false),
            ]);
    }
}