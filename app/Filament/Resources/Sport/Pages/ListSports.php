<?php

namespace App\Filament\Resources\Sport\Pages;

use App\Filament\Resources\Sport\SportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSports extends ListRecords
{
    public static string $resource = SportResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}