<?php

namespace Database\Factories;

use App\Models\AttendanceEvent;
use App\Models\FraudFlag;
use App\Models\Intern;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FraudFlag>
 */
class FraudFlagFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'intern_id' => Intern::factory(),
            'evidence_id' => null,
            'attendance_event_id' => AttendanceEvent::factory(),
            'remote_check_in_id' => null,
            'score' => fake()->randomFloat(2, 0, 100),
            'scorer' => 'stub',
        ];
    }
}
