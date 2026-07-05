<?php

namespace App\Filament\Resources\Sport\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class SportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('slug')->unique()->required(),
                TextInput::make('icon')->nullable(),
                TextInput::make('description')->nullable(),
                KeyValue::make('skill_levels')
                    ->keyLabel('Level Value')
                    ->valueLabel('Level Name')
                    ->nullable(),
            ]);
    }
}