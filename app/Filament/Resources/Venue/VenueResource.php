<?php

namespace App\Filament\Resources\Venue;

use App\Filament\Resources\Venue\Pages\CreateVenue;
use App\Filament\Resources\Venue\Pages\EditVenue;
use App\Filament\Resources\Venue\Pages\ListVenues;
use App\Filament\Resources\Venue\Schemas\VenueForm;
use App\Filament\Resources\Venue\Tables\VenuesTable;
use App\Models\Venue;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VenueResource extends Resource
{
    protected static ?string $model = Venue::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Konten Platform';
    }

    public static function form(Schema $schema): Schema
    {
        return VenueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenuesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListVenues::route('/'),
            'create' => CreateVenue::route('/create'),
            'edit'   => EditVenue::route('/{record}/edit'),
        ];
    }
}