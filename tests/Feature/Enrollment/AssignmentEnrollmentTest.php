<?php

namespace Tests\Feature\Enrollment;

use App\Models\Assignment;
use App\Models\Intern;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AssignmentEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_signed_in_manager_can_assign_an_intern_to_a_site(): void
    {
        $this->actingAs(User::factory()->create());
        $intern = Intern::factory()->create();
        $site = Site::factory()->create();

        $this->post(route('assignments.store'), [
            'intern_id' => $intern->id,
            'site_id' => $site->id,
        ])->assertRedirect(route('assignments.index'));

        $this->assertDatabaseHas('assignments', [
            'intern_id' => $intern->id,
            'site_id' => $site->id,
        ]);
    }

    public function test_the_same_intern_cannot_be_assigned_to_the_same_site_twice(): void
    {
        $this->actingAs(User::factory()->create());
        $intern = Intern::factory()->create();
        $site = Site::factory()->create();
        Assignment::factory()->create(['intern_id' => $intern->id, 'site_id' => $site->id]);

        $this->post(route('assignments.store'), [
            'intern_id' => $intern->id,
            'site_id' => $site->id,
        ])->assertSessionHasErrors('site_id');

        $this->assertDatabaseCount('assignments', 1);
    }

    public function test_an_intern_can_be_assigned_to_a_second_site(): void
    {
        $this->actingAs(User::factory()->create());
        $intern = Intern::factory()->create();
        $first = Site::factory()->create();
        $second = Site::factory()->create();
        Assignment::factory()->create(['intern_id' => $intern->id, 'site_id' => $first->id]);

        $this->post(route('assignments.store'), [
            'intern_id' => $intern->id,
            'site_id' => $second->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('assignments', 2);
    }

    public function test_unknown_or_malformed_ids_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('assignments.store'), [
            'intern_id' => (string) Str::uuid(),
            'site_id' => (string) Str::uuid(),
        ])->assertSessionHasErrors(['intern_id', 'site_id']);

        $this->post(route('assignments.store'), [
            'intern_id' => 'not-a-uuid',
            'site_id' => '',
        ])->assertSessionHasErrors(['intern_id', 'site_id']);

        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_a_deleted_intern_cannot_be_assigned(): void
    {
        $this->actingAs(User::factory()->create());
        $intern = Intern::factory()->create();
        $site = Site::factory()->create();
        $intern->delete();

        $this->post(route('assignments.store'), [
            'intern_id' => $intern->id,
            'site_id' => $site->id,
        ])->assertSessionHasErrors('intern_id');

        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_the_assignment_page_lists_names_and_choices(): void
    {
        $this->actingAs(User::factory()->create());
        $intern = Intern::factory()->create(['name' => 'Assigned Intern']);
        $site = Site::factory()->create(['name' => 'Assigned Site']);
        Assignment::factory()->create(['intern_id' => $intern->id, 'site_id' => $site->id]);

        $this->get(route('assignments.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('assignments/index')
                ->has('assignments', 1)
                ->where('assignments.0.intern', 'Assigned Intern')
                ->where('assignments.0.site', 'Assigned Site')
                ->has('interns', 1)
                ->has('sites', 1)
                ->missing('interns.0.pin_hash'));
    }

    public function test_a_signed_out_visitor_cannot_open_the_assignment_screens(): void
    {
        $intern = Intern::factory()->create();
        $site = Site::factory()->create();

        $this->get(route('assignments.index'))->assertRedirect(route('login'));

        $this->post(route('assignments.store'), [
            'intern_id' => $intern->id,
            'site_id' => $site->id,
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('assignments', 0);
    }
}
