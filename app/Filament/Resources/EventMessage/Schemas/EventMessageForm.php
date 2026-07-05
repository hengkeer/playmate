<?php

namespace App\Filament\Resources\EventMessage\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

final class EventMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')->label('Event')->relationship('event', 'title')->searchable()->preload()->required(),
                Select::make('user_id')->label('User')->relationship('user', 'name')->searchable()->preload()->required(),
                Textarea::make('content')->nullable(),
                Select::make('type')->options(['text' => 'Text', 'image' => 'Image', 'file' => 'File']),
            ]);
    }
}