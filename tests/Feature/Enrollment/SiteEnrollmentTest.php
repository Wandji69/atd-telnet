<?php

namespace Tests\Feature\Enrollment;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SiteEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_signed_in_manager_can_create_a_site_with_a_radius(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('sites.store'), [
            'name' => 'Test Site',
            'latitude' => 4.1559,
            'longitude' => 9.2417,
            'radius_meters' => 250,
        ])->assertRedirect(route('sites.index'));

        $site = Site::firstOrFail();

        $this->assertSame('Test Site', $site->name);
        $this->assertEqualsWithDelta(4.1559, $site->latitude, 0.00001);
        $this->assertEqualsWithDelta(9.2417, $site->longitude, 0.00001);
        $this->assertSame(250, $site->radius_meters);
    }

    public function test_omitting_the_radius_stores_100(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('sites.store'), [
            'name' => 'Default Radius Site',
            'latitude' => 4.1559,
            'longitude' => 9.2417,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sites', [
            'name' => 'Default Radius Site',
            'radius_meters' => 100,
        ]);
    }

    public function test_an_empty_radius_field_stores_100(): void
    {
        $this->actingAs(User::factory()->create());

        // A browser sends an empty field as an empty string.
        $this->post(route('sites.store'), [
            'name' => 'Empty Radius Site',
            'latitude' => 4.1559,
            'longitude' => 9.2417,
            'radius_meters' => '',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sites', [
            'name' => 'Empty Radius Site',
            'radius_meters' => 100,
        ]);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function invalidSites(): array
    {
        $valid = ['name' => 'Bad Site', 'latitude' => 4.1, 'longitude' => 9.2];

        return [
            'missing name' => [array_merge($valid, ['name' => '']), 'name'],
            'latitude too high' => [array_merge($valid, ['latitude' => 91]), 'latitude'],
            'latitude too low' => [array_merge($valid, ['latitude' => -91]), 'latitude'],
            'latitude not a number' => [array_merge($valid, ['latitude' => 'north']), 'latitude'],
            'longitude too high' => [array_merge($valid, ['longitude' => 181]), 'longitude'],
            'longitude too low' => [array_merge($valid, ['longitude' => -181]), 'longitude'],
            'missing longitude' => [array_merge($valid, ['longitude' => '']), 'longitude'],
            'radius zero' => [array_merge($valid, ['radius_meters' => 0]), 'radius_meters'],
            'radius negative' => [array_merge($valid, ['radius_meters' => -5]), 'radius_meters'],
            'radius not whole' => [array_merge($valid, ['radius_meters' => 12.5]), 'radius_meters'],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    #[DataProvider('invalidSites')]
    public function test_an_invalid_site_is_rejected_and_not_stored(array $payload, string $field): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('sites.store'), $payload)->assertSessionHasErrors($field);

        $this->assertDatabaseCount('sites', 0);
    }

    public function test_the_site_list_shows_the_radius(): void
    {
        $this->actingAs(User::factory()->create());
        Site::factory()->create(['name' => 'Listed Site', 'radius_meters' => 75]);

        $this->get(route('sites.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('sites/index')
                ->has('sites', 1)
                ->where('sites.0.name', 'Listed Site')
                ->where('sites.0.radius_meters', 75));
    }

    public function test_a_signed_out_visitor_cannot_open_the_site_screens(): void
    {
        $this->get(route('sites.index'))->assertRedirect(route('login'));

        $this->post(route('sites.store'), [
            'name' => 'Guest Site',
            'latitude' => 4.1,
            'longitude' => 9.2,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('sites', 0);
    }
}
