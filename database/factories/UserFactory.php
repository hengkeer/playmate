<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'                  => fake()->name(),
            'email'                 => fake()->unique()->safeEmail(),
            'email_verified_at'     => now(),
            'password'              => static::$password ??= Hash::make('password'),
            'remember_token'        => Str::random(10),
            'photo_url'             => null,
            'bio'                   => fake()->optional()->sentence(),
            'gender'                => fake()->randomElement(['male', 'female', 'other']),
            'age_range'             => fake()->randomElement(['18-25', '26-35', '36-45', '46+']),
            'home_address'          => fake()->streetAddress() . ', ' . fake()->city(),
            'latitude'              => fake()->latitude(-6.3, -6.1),
            'longitude'             => fake()->longitude(106.7, 107.0),
            'play_style'            => fake()->randomElement(['casual', 'competitive', 'training']),
            'last_active_at'        => fake()->dateTimeBetween('-30 days', 'now'),
            'total_events_joined'   => fake()->numberBetween(0, 50),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
