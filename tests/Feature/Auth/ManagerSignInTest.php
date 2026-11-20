<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\ManagerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class ManagerSignInTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'local-test-password-42';

    private function seedManager(): User
    {
        config([
            'manager.name' => 'Seeded Manager',
            'manager.email' => 'seeded-manager@example.test',
            'manager.password' => self::PASSWORD,
        ]);

        $this->seed(ManagerSeeder::class);

        return User::where('email', 'seeded-manager@example.test')->firstOrFail();
    }

    public function test_a_manager_with_valid_credentials_reaches_a_signed_in_page(): void
    {
        $manager = $this->seedManager();

        $this->post(route('login.store'), [
            'email' => $manager->email,
            'password' => self::PASSWORD,
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($manager);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('auth.user.id', $manager->id)
                ->where('auth.user.email', $manager->email));
    }

    public function test_a_wrong_password_stays_on_the_sign_in_screen_and_shows_an_error(): void
    {
        $manager = $this->seedManager();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $manager->email,
                'password' => 'not-the-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->followingRedirects()
            ->from(route('login'))
            ->post(route('login.store'), [
                'email' => $manager->email,
                'password' => 'not-the-password',
            ])
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/login')
                ->has('errors.email'));
    }

    public function test_a_signed_out_request_to_a_manager_route_redirects_to_sign_in(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_a_manager_can_sign_out_and_loses_access(): void
    {
        $manager = $this->seedManager();

        $this->actingAs($manager)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_the_password_is_stored_hashed_and_never_sent_to_the_browser(): void
    {
        $manager = $this->seedManager();

        $stored = $manager->getRawOriginal('password');

        $this->assertNotSame(self::PASSWORD, $stored);
        $this->assertTrue(Hash::isHashed($stored));
        $this->assertTrue(Hash::check(self::PASSWORD, $stored));

        $this->actingAs($manager);

        // The page props are embedded in the HTML, so this covers what the
        // browser receives as well as the `auth.user` prop itself.
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($stored, false)
            ->assertDontSee(self::PASSWORD, false)
            ->assertInertia(fn (Assert $page) => $page
                ->has('auth.user')
                ->missing('auth.user.password'));
    }

    public function test_the_seeder_creates_one_manager_and_is_safe_to_run_twice(): void
    {
        $this->seedManager();
        $this->seed(ManagerSeeder::class);

        $this->assertSame(1, User::count());
        $this->assertNotNull(User::first()->email_verified_at);
    }

    public function test_the_seeder_refuses_a_missing_or_placeholder_password(): void
    {
        foreach ([null, '', 'change-me'] as $password) {
            config(['manager.password' => $password]);

            try {
                $this->seed(ManagerSeeder::class);
                $this->fail('Seeder accepted password: '.var_export($password, true));
            } catch (RuntimeException) {
                $this->assertSame(0, User::count());
            }
        }
    }

    public function test_public_registration_stays_disabled(): void
    {
        $this->get('/register')->assertNotFound();
    }
}
