<?php

namespace App\Filament\Resources\EventParticipants\Tables;

use App\Models\EventParticipant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class EventParticipantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->label('ID'),
                TextColumn::make('event.title')->label('Event')->searchable()->limit(40),
                TextColumn::make('user.name')->label('User')->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending'  => 'warning',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),
                IconColumn::make('is_invite')->label('Challenge Invite')->boolean(),
                TextColumn::make('slot_number')->label('Slot')->sortable(),
                TextColumn::make('joined_at')->label('Joined At')->dateTime('M j, Y H:i')->sortable(),
                TextColumn::make('created_at')->dateTime('M j, Y H:i')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
