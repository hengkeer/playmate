<?php

namespace App\Filament\Resources\EventParticipants\Pages;

use App\Filament\Resources\EventParticipants\EventParticipantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventParticipants extends ListRecords
{
    public static string $resource = EventParticipantResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}