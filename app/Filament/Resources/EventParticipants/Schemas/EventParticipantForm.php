<?php

namespace App\Filament\Resources\EventParticipants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class EventParticipantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),

                Toggle::make('is_invite')
                    ->label('Challenge Invite')
                    ->helperText('True if this participant was invited by the host (challenge) rather than requesting to join themselves.'),

                TextInput::make('slot_number')
                    ->label('Slot Number')
                    ->numeric()
                    ->minValue(1)
                    ->nullable(),
            ]);
    }
}
