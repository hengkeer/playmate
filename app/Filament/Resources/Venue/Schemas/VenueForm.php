<?php

namespace App\Filament\Resources\Venue\Schemas;

use Fahiem\FilamentPinpoint\Pinpoint;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class VenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                Select::make('sport_id')->label('Sport')->relationship('sport', 'name')->searchable()->preload()->required(),
                TextInput::make('address')->required(),
                TextInput::make('area')->required(),
                Pinpoint::make('location')
                    ->provider('leaflet')
                    ->defaultLocation(-6.2088, 106.8456)
                    ->height(350)
                    ->latField('latitude')
                    ->lngField('longitude')
                    ->addressField('address')
                    ->searchable()
                    ->draggable(),
                TextInput::make('open_hours')->nullable(),
                TextInput::make('price_estimate')->numeric()->nullable()->label('Price Estimate (IDR/hr)'),
                TextInput::make('contact')->nullable(),
                FileUpload::make('image_url')
                    ->label('Primary Image')
                    ->image()
                    ->directory('venues')
                    ->nullable(),
                FileUpload::make('images')
                    ->label('Photo Gallery')
                    ->image()
                    ->multiple()
                    ->directory('venues')
                    ->reorderable()
                    ->nullable(),
                Textarea::make('description')->nullable(),
                Toggle::make('is_active')->default(true),
            ]);
    }
}