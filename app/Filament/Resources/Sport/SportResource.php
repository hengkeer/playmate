<?php

namespace App\Filament\Resources\Sport;

use App\Filament\Resources\Sport\Pages\CreateSport;
use App\Filament\Resources\Sport\Pages\EditSport;
use App\Filament\Resources\Sport\Pages\ListSports;
use App\Filament\Resources\Sport\Schemas\SportForm;
use App\Filament\Resources\Sport\Tables\SportsTable;
use App\Models\Sport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SportResource extends Resource
{
    protected static ?string $model = Sport::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Konten Platform';
    }

    public static function form(Schema $schema): Schema
    {
        return SportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSports::route('/'),
            'create' => CreateSport::route('/create'),
            'edit'   => EditSport::route('/{record}/edit'),
        ];
    }
}