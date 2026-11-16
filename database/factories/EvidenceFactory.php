<?php

namespace Database\Factories;

use App\Models\AttendanceEvent;
use App\Models\Evidence;
use App\Models\Intern;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Metadata only. No file is written by the factory.
 *
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'intern_id' => Intern::factory(),
            'attendance_event_id' => AttendanceEvent::factory(),
            'remote_check_in_id' => null,
            'object_key' => 'evidence/'.Str::uuid().'.jpg',
            'captured_at' => now(),
        ];
    }
}
