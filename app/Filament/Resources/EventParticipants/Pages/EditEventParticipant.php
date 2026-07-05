<?php

namespace App\Filament\Resources\EventParticipants\Pages;

use App\Filament\Resources\EventParticipants\EventParticipantResource;
use Filament\Resources\Pages\EditRecord;

class EditEventParticipant extends EditRecord
{
    public static string $resource = EventParticipantResource::class;
}