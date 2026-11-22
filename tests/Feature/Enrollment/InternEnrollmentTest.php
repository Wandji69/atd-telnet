<?php

namespace Tests\Feature\Enrollment;

use App\Models\Intern;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InternEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private const PIN = '482915';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('evidence');
    }

    /**
     * A real 1x1 PNG, so the upload passes the mime check without needing GD.
     */
    private function photo(string $name = 'reference.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Intern',
            'pin' => self::PIN,
            'reference_photo' => $this->photo(),
        ], $overrides);
    }

    public function test_a_signed_in_manager_can_enroll_an_intern(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('interns.store'), $this->validPayload())
            ->assertRedirect(route('interns.index'));

        $intern = Intern::firstOrFail();

        $this->assertSame('Test Intern', $intern->name);
        $this->assertTrue(Hash::check(self::PIN, $intern->pin_hash));
    }

    public function test_the_reference_photo_is_on_the_evidence_disk_and_the_intern_points_at_it(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('interns.store'), $this->validPayload());

        $intern = Intern::firstOrFail();

        $this->assertNotNull($intern->reference_photo_key);
        $this->assertStringStartsWith('reference/', $intern->reference_photo_key);
        $this->assertStringContainsString($intern->id, $intern->reference_photo_key);
        Storage::disk('evidence')->assertExists($intern->reference_photo_key);
    }

    public function test_the_database_holds_the_pin_hash_and_never_the_pin(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('interns.store'), $this->validPayload());

        $intern = Intern::firstOrFail();

        $this->assertNotSame(self::PIN, $intern->pin_hash);
        $this->assertDatabaseMissing('interns', ['pin_hash' => self::PIN]);

        foreach ($intern->getAttributes() as $column => $value) {
            $this->assertNotSame(self::PIN, $value, "Column {$column} holds the raw PIN.");
        }
    }

    /**
     * @return array<string, array{0: string|null}>
     */
    public static function invalidPins(): array
    {
        return [
            'five digits' => ['48291'],
            'seven digits' => ['4829153'],
            'letters' => ['abcdef'],
            'digits and a letter' => ['48291a'],
            'with a space' => ['482 91'],
            'empty' => [''],
            'missing' => [null],
        ];
    }

    #[DataProvider('invalidPins')]
    public function test_a_pin_that_is_not_six_digits_is_rejected_and_not_stored(?string $pin): void
    {
        $this->actingAs(User::factory()->create());

        $this->from(route('interns.index'))
            ->post(route('interns.store'), $this->validPayload(['pin' => $pin]))
            ->assertSessionHasErrors('pin')
            ->assertSessionMissing('_old_input.pin');

        $this->assertDatabaseCount('interns', 0);
        $this->assertSame([], Storage::disk('evidence')->allFiles());
    }

    public function test_a_reference_photo_is_required_and_must_be_an_image(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('interns.store'), $this->validPayload(['reference_photo' => null]))
            ->assertSessionHasErrors('reference_photo');

        $this->post(route('interns.store'), $this->validPayload([
            'reference_photo' => UploadedFile::fake()->createWithContent('notes.txt', 'not an image'),
        ]))->assertSessionHasErrors('reference_photo');

        $this->assertDatabaseCount('interns', 0);
        $this->assertSame([], Storage::disk('evidence')->allFiles());
    }

    public function test_a_name_is_required(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('interns.store'), $this->validPayload(['name' => '']))
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('interns', 0);
    }

    public function test_the_intern_list_shows_safe_fields_only(): void
    {
        $this->actingAs(User::factory()->create());
        $intern = Intern::factory()->create(['name' => 'Listed Intern']);

        $this->get(route('interns.index'))
            ->assertOk()
            ->assertDontSee($intern->pin_hash, false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('interns/index')
                ->has('interns', 1)
                ->where('interns.0.name', 'Listed Intern')
                ->where('interns.0.has_reference_photo', true)
                ->missing('interns.0.pin_hash')
                ->missing('interns.0.reference_photo_key'));
    }

    public function test_a_signed_out_visitor_cannot_open_the_intern_screens(): void
    {
        $this->get(route('interns.index'))->assertRedirect(route('login'));

        $this->post(route('interns.store'), $this->validPayload())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('interns', 0);
        $this->assertSame([], Storage::disk('evidence')->allFiles());
    }
}
