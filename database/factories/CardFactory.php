<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Intern;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Status, counters, and flags are left to the database defaults.
 *
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'intern_id' => Intern::factory(),
        ];
    }
}
