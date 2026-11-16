<?php

namespace Database\Factories;

use App\Enums\AttendanceKind;
use App\Enums\CardStatus;
use App\Models\AttendanceEvent;
use App\Models\Device;
use App\Models\Intern;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * A terminal check-in by default.
 *
 * @extends Factory<AttendanceEvent>
 */
class AttendanceEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'intern_id' => Intern::factory(),
            'kind' => AttendanceKind::Terminal,
            'status' => CardStatus::CheckedIn,
            'occurred_at' => now(),
            'photo_key' => 'evidence/'.Str::uuid().'.jpg',
        ];
    }
}
