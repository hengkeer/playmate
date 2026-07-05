<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventMessage;
use App\Models\EventParticipant;
use App\Models\Field;
use App\Models\Sport;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\UserSport;
use App\Models\Venue;
use App\Models\VenueSlot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DataSeeder::class,
        ]);
    }
}
