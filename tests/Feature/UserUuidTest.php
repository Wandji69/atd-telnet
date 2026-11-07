<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserUuidTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_supplied_uuid_is_stored_unchanged(): void
    {
        $supplied = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

        $user = User::factory()->create([
            'id' => $supplied,
        ]);

        $this->assertSame($supplied, $user->id);
        $this->assertSame($supplied, $user->fresh()->id);
    }

    public function test_a_user_without_an_id_receives_a_uuid(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Str::isUuid($user->id));
    }
}
