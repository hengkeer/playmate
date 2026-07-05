<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Sport;
use App\Models\User;
use App\Models\Venue;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalUsers  = User::count();
        $totalEvents = Event::count();
        $totalSports = Sport::count();
        $totalVenues = Venue::count();

        return [
            Stat::make('Total Users', number_format($totalUsers))
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Total Events', number_format($totalEvents))
                ->color('warning')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Sports', number_format($totalSports))
                ->color('warning')
                ->icon('heroicon-o-trophy'),

            Stat::make('Venues', number_format($totalVenues))
                ->color('danger')
                ->icon('heroicon-o-map-pin'),
        ];
    }
}
