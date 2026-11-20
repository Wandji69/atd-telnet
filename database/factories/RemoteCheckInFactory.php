<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Intern;
use App\Models\RemoteCheckIn;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RemoteCheckIn>
 */
class RemoteCheckInFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'intern_id' => Intern::factory(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'cell_tower_ref' => fake()->numerify('624-01-#####-####'),
            'occurred_at' => now(),
            'photo_key' => 'evidence/'.Str::uuid().'.jpg',
        ];
    }
}
