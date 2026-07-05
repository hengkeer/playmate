<?php

namespace App\Filament\Resources\EventMessage;

use App\Filament\Resources\EventMessage\Pages\ListEventMessages;
use App\Filament\Resources\EventMessage\Schemas\EventMessageForm;
use App\Filament\Resources\EventMessage\Tables\EventMessagesTable;
use App\Models\EventMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventMessageResource extends Resource
{
    protected static ?string $model = EventMessage::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Event & Komunitas';
    }

    public static function form(Schema $schema): Schema
    {
        return EventMessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventMessagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventMessages::route('/'),
        ];
    }
}