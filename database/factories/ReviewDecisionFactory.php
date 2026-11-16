<?php

namespace Database\Factories;

use App\Enums\ReviewAction;
use App\Models\AttendanceEvent;
use App\Models\ReviewDecision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewDecision>
 */
class ReviewDecisionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'attendance_event_id' => AttendanceEvent::factory(),
            'remote_check_in_id' => null,
            'action' => ReviewAction::Override,
            'reason' => fake()->sentence(),
        ];
    }
}
