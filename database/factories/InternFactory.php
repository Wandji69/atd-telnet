<?php

namespace Database\Factories;

use App\Models\Intern;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Synthetic interns only. Never load real names or photos.
 *
 * @extends Factory<Intern>
 */
class InternFactory extends Factory
{
    /**
     * The hash of the default test PIN, computed once.
     */
    protected static ?string $pinHash;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'pin_hash' => static::$pinHash ??= Hash::make('123456'),
            'reference_photo_key' => 'reference/'.Str::uuid().'.jpg',
        ];
    }
}
