<?php

namespace App\Filament\Resources\EventMessage\Pages;

use App\Filament\Resources\EventMessage\EventMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListEventMessages extends ListRecords
{
    public static string $resource = EventMessageResource::class;
}