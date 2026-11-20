<?php

use App\Enums\CardStatus;
use App\Models\Assignment;
use App\Models\AttendanceEvent;
use App\Models\Card;
use App\Models\Device;
use App\Models\Evidence;
use App\Models\FraudFlag;
use App\Models\Intern;
use App\Models\Notification;
use App\Models\RemoteCheckIn;
use App\Models\ReviewDecision;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * Every prototype table, mapped to its model.
 *
 * @return array<string, array{0: string, 1: class-string<Model>}>
 */
function prototypeTables(): array
{
    return [
        'users' => ['users', User::class],
        'devices' => ['devices', Device::class],
        'interns' => ['interns', Intern::class],
        'sites' => ['sites', Site::class],
        'assignments' => ['assignments', Assignment::class],
        'cards' => ['cards', Card::class],
        'attendance_events' => ['attendance_events', AttendanceEvent::class],
        'remote_check_ins' => ['remote_check_ins', RemoteCheckIn::class],
        'evidence' => ['evidence', Evidence::class],
        'fraud_flags' => ['fraud_flags', FraudFlag::class],
        'review_decisions' => ['review_decisions', ReviewDecision::class],
        'notifications' => ['notifications', Notification::class],
    ];
}

/**
 * Tables the terminal pulls or pushes. They keep a soft-delete timestamp.
 *
 * @return list<string>
 */
function syncedTables(): array
{
    return [
        'interns',
        'sites',
        'assignments',
        'cards',
        'attendance_events',
        'remote_check_ins',
        'evidence',
    ];
}

test('migrating creates every prototype table', function (string $table) {
    expect(Schema::hasTable($table))->toBeTrue();
})->with(fn () => array_map(fn ($row) => $row[0], prototypeTables()));

test('every primary key is a uuid column without auto-increment', function (string $table) {
    $id = collect(Schema::getColumns($table))->firstWhere('name', 'id');

    expect($id)->not->toBeNull()
        ->and($id['auto_increment'])->toBeFalse();

    // Postgres has a native uuid type. The SQLite test database stores
    // UUIDs as text, so the type is only asserted on Postgres.
    if (DB::getDriverName() === 'pgsql') {
        expect($id['type_name'])->toBe('uuid');
    }
})->with(fn () => array_map(fn ($row) => $row[0], prototypeTables()));

test('synced tables have a nullable deleted_at column', function (string $table) {
    $column = collect(Schema::getColumns($table))->firstWhere('name', 'deleted_at');

    expect($column)->not->toBeNull()
        ->and($column['nullable'])->toBeTrue();
})->with(fn () => syncedTables());

test('a factory persists one row of each table with a uuid id', function (string $table, string $model) {
    $row = $model::factory()->create();

    expect(Str::isUuid($row->getKey()))->toBeTrue()
        ->and(DB::table($table)->where('id', $row->getKey())->exists())->toBeTrue();
})->with(fn () => prototypeTables());

test('a supplied uuid is stored unchanged', function (string $table, string $model) {
    $supplied = (string) Str::uuid();

    $model::factory()->create(['id' => $supplied]);

    expect(DB::table($table)->where('id', $supplied)->exists())->toBeTrue();
})->with(fn () => prototypeTables());

test('models reject a non-uuid primary key', function (string $table, string $model) {
    $before = DB::table($table)->count();

    expect(fn () => $model::factory()->create(['id' => 'not-a-uuid']))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => $model::factory()->create(['id' => '12345']))
        ->toThrow(InvalidArgumentException::class);

    // Compare counts rather than querying the bad ids: Postgres refuses
    // a non-UUID literal in a uuid column even inside a WHERE clause.
    expect(DB::table($table)->count())->toBe($before);
})->with(fn () => prototypeTables());

test('sites radius defaults to 100 meters when omitted', function () {
    $site = Site::query()->create([
        'name' => 'Synthetic site',
        'latitude' => 4.0511,
        'longitude' => 9.7679,
    ]);

    expect($site->fresh()?->radius_meters)->toBe(100)
        ->and(DB::table('sites')->where('id', $site->id)->value('radius_meters'))->toEqual(100);
});

test('cards status defaults to CHECKED_OUT when omitted', function () {
    $card = Card::query()->create(['intern_id' => Intern::factory()->create()->id]);

    expect($card->fresh()?->status)->toBe(CardStatus::CheckedOut)
        ->and(DB::table('cards')->where('id', $card->id)->value('status'))->toBe('CHECKED_OUT');
});

test('intern serialization hides the pin hash', function () {
    $intern = Intern::factory()->create();

    expect($intern->toArray())->not->toHaveKey('pin_hash');
});

test('soft-deleting a synced row keeps it and sets deleted_at', function () {
    $intern = Intern::factory()->create();

    $intern->delete();

    expect(DB::table('interns')->where('id', $intern->id)->value('deleted_at'))->not->toBeNull()
        ->and(Intern::withTrashed()->find($intern->id))->not->toBeNull();
});
