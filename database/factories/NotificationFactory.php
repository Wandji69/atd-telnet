<?php

namespace Database\Factories;

use App\Models\FraudFlag;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fraud_flag_id' => FraudFlag::factory(),
            'channel' => 'log',
            'payload' => ['score' => 70],
            'status' => 'stored',
        ];
    }
}
