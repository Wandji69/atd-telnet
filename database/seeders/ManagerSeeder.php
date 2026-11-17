<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Creates one synthetic manager for local development.
 *
 * The password is read from MANAGER_PASSWORD in the developer's own .env and
 * is hashed by the User model's `hashed` cast. Running the seeder again
 * updates the same account instead of adding a second one.
 */
class ManagerSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('ManagerSeeder is for local development only.');
        }

        $password = config('manager.password');

        if (! is_string($password) || $password === '' || $password === 'change-me') {
            throw new RuntimeException(
                'Replace the MANAGER_PASSWORD placeholder in your .env before running ManagerSeeder.'
            );
        }

        // Fortify lowercases the email typed at sign-in, so store it lowercase.
        $manager = User::query()->firstOrNew(['email' => Str::lower(config('manager.email'))]);

        $manager->forceFill([
            'name' => config('manager.name'),
            'password' => $password,
            'email_verified_at' => $manager->email_verified_at ?? now(),
        ])->save();
    }
}
