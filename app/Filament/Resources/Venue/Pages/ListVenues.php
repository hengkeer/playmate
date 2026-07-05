<?php

namespace App\Filament\Resources\Venue\Pages;

use App\Filament\Resources\Venue\VenueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVenues extends ListRecords
{
    public static string $resource = VenueResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}