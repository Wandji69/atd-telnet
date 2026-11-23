<?php

use App\Models\Assignment;
use App\Models\Card;
use App\Models\Device;
use App\Models\Intern;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.sync.dev_token' => 'test-sync-token']);
});

/**
 * Sends a pull as the terminal would.
 *
 * @param  array<string, mixed>  $extra
 * @return TestResponse<JsonResponse>
 */
function pull(?int $lastPulledAt, array $extra = []): TestResponse
{
    return test()
        ->withToken('test-sync-token')
        ->postJson('/api/sync/pull', ['lastPulledAt' => $lastPulledAt, ...$extra]);
}

/**
 * The ids in one changes array, for example changes.interns.created.
 *
 * @return list<string>
 */
function idsIn(TestResponse $response, string $path): array
{
    return collect($response->json($path))->pluck('id')->values()->all();
}

test('a first pull returns the existing intern, site, assignment, and card in created', function () {
    $assignment = Assignment::factory()->create();
    $card = Card::factory()->create(['intern_id' => $assignment->intern_id]);

    $response = pull(null)->assertOk();

    expect(idsIn($response, 'changes.interns.created'))->toBe([$assignment->intern_id])
        ->and(idsIn($response, 'changes.sites.created'))->toBe([$assignment->site_id])
        ->and(idsIn($response, 'changes.assignments.created'))->toBe([$assignment->id])
        ->and(idsIn($response, 'changes.cards.created'))->toBe([$card->id]);

    foreach (['interns', 'sites', 'assignments', 'cards'] as $table) {
        expect($response->json("changes.$table.updated"))->toBe([])
            ->and($response->json("changes.$table.deleted"))->toBe([]);
    }
});

test('a first pull leaves out soft-deleted rows', function () {
    $kept = Intern::factory()->create();
    Intern::factory()->create()->delete();

    $response = pull(null)->assertOk();

    expect(idsIn($response, 'changes.interns.created'))->toBe([$kept->id])
        ->and($response->json('changes.interns.deleted'))->toBe([]);
});

test('a later pull returns only changes made after lastPulledAt', function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00:00'));
    $site = Site::factory()->create();
    $oldIntern = Intern::factory()->create();

    // The first pull happens a minute after the rows were written. A row
    // from the same second as a pull is deliberately sent again next time.
    $this->travelTo(Carbon::parse('2026-11-23 10:01:00'));
    $timestamp = pull(null)->json('timestamp');

    $this->travelTo(Carbon::parse('2026-11-23 10:05:00'));
    $site->update(['name' => 'Renamed site']);
    $newIntern = Intern::factory()->create();

    $response = pull($timestamp)->assertOk();

    expect(idsIn($response, 'changes.sites.created'))->toBe([])
        ->and(idsIn($response, 'changes.sites.updated'))->toBe([$site->id])
        ->and(idsIn($response, 'changes.interns.created'))->toBe([$newIntern->id])
        ->and(idsIn($response, 'changes.interns.updated'))->toBe([])
        ->and(idsIn($response, 'changes.interns.created'))->not->toContain($oldIntern->id);
});

test('a soft-deleted row appears only in deleted', function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00:00'));
    $intern = Intern::factory()->create();

    $this->travelTo(Carbon::parse('2026-11-23 10:01:00'));
    $timestamp = pull(null)->json('timestamp');

    $this->travelTo(Carbon::parse('2026-11-23 10:05:00'));
    $intern->delete();

    $response = pull($timestamp)->assertOk();

    expect($response->json('changes.interns.deleted'))->toBe([$intern->id])
        ->and(idsIn($response, 'changes.interns.created'))->toBe([])
        ->and(idsIn($response, 'changes.interns.updated'))->toBe([]);
});

test('a row written in the same second as the last pull is not skipped', function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00:00.200'));
    $timestamp = pull(null)->json('timestamp');

    // Stored as 10:00:00 because database timestamps keep whole seconds.
    $this->travelTo(Carbon::parse('2026-11-23 10:00:00.700'));
    $intern = Intern::factory()->create();

    $response = pull($timestamp)->assertOk();

    expect(idsIn($response, 'changes.interns.created'))->toContain($intern->id);
});

test('intern records never include the pin hash', function () {
    $intern = Intern::factory()->create();

    $response = pull(null)->assertOk();

    expect($response->json('changes.interns.created.0'))->not->toHaveKey('pin_hash')
        ->and($response->getContent())->not->toContain((string) $intern->pin_hash);
});

test('timestamp is an integer number of milliseconds', function () {
    $this->travelTo(Carbon::parse('2026-11-23 10:00:00.750'));

    $timestamp = pull(null)->assertOk()->json('timestamp');

    expect($timestamp)->toBeInt()
        ->toBe(Carbon::parse('2026-11-23 10:00:00')->getTimestampMs());
});

test('a pull with a known device_id sets that device last_synced_at', function () {
    $device = Device::factory()->create(['last_synced_at' => null]);

    pull(null, ['device_id' => $device->id])->assertOk();

    expect($device->fresh()?->last_synced_at)->not->toBeNull();
});

test('a missing or wrong bearer token receives 401', function () {
    $this->postJson('/api/sync/pull', ['lastPulledAt' => null])->assertUnauthorized();

    $this->withToken('wrong-token')
        ->postJson('/api/sync/pull', ['lastPulledAt' => null])
        ->assertUnauthorized();
});

test('an invalid lastPulledAt receives 422', function () {
    pull(null, ['lastPulledAt' => 'yesterday'])->assertUnprocessable();
});
