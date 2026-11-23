<?php

namespace App\Actions\Sync;

use App\Models\Assignment;
use App\Models\Card;
use App\Models\Device;
use App\Models\Intern;
use App\Models\Model;
use App\Models\Site;
use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Builds a WatermelonDB pull response: the rows of each pulled table that
 * changed since the terminal's last pull.
 *
 * Database timestamps have one-second precision, so the returned timestamp is
 * rounded down to the start of the second and later pulls compare with ">=".
 * A row written in that same second may be sent twice, which WatermelonDB
 * tolerates. A row is never skipped.
 */
class PullChanges
{
    /**
     * Pulled tables and their models. Secrets are kept out of the payload by
     * each model's hidden attributes (for example interns.pin_hash).
     *
     * @var array<string, class-string<Model>>
     */
    private const TABLES = [
        'interns' => Intern::class,
        'sites' => Site::class,
        'assignments' => Assignment::class,
        'cards' => Card::class,
    ];

    /**
     * @return array{changes: array<string, array{created: list<array<string, mixed>>, updated: list<array<string, mixed>>, deleted: list<string>}>, timestamp: int}
     */
    public function __invoke(?int $lastPulledAt, ?string $deviceId): array
    {
        // Captured before reading, so a change made during this pull is
        // picked up by the next one.
        $serverTime = Carbon::now()->startOfSecond();

        $since = $lastPulledAt === null
            ? null
            : Carbon::createFromTimestampMs($lastPulledAt)->startOfSecond();

        $changes = [];

        foreach (self::TABLES as $table => $model) {
            $changes[$table] = $this->changesFor($model, $since);
        }

        if ($deviceId !== null) {
            Device::query()->whereKey($deviceId)->update(['last_synced_at' => $serverTime]);
        }

        return [
            'changes' => $changes,
            'timestamp' => (int) $serverTime->getTimestampMs(),
        ];
    }

    /**
     * Splits one table's changes into created, updated, and deleted.
     * A row appears in at most one of the three.
     *
     * @param  class-string<Model>  $model
     * @return array{created: list<array<string, mixed>>, updated: list<array<string, mixed>>, deleted: list<string>}
     */
    private function changesFor(string $model, ?Carbon $since): array
    {
        // First pull: every current row is new to the terminal.
        if ($since === null) {
            return [
                'created' => $this->records($model::query()->get()->all()),
                'updated' => [],
                'deleted' => [],
            ];
        }

        // The default query already leaves out soft-deleted rows.
        $created = $model::query()
            ->where('created_at', '>=', $since)
            ->get()->all();

        $updated = $model::query()
            ->where('created_at', '<', $since)
            ->where('updated_at', '>=', $since)
            ->get()->all();

        /** @var list<string> $deleted */
        $deleted = $model::query()
            ->withoutGlobalScopes()
            ->whereNotNull('deleted_at')
            ->where('deleted_at', '>=', $since)
            ->pluck('id')
            ->all();

        return [
            'created' => $this->records($created),
            'updated' => $this->records($updated),
            'deleted' => $deleted,
        ];
    }

    /**
     * Raw WatermelonDB records: column values, with dates as epoch
     * milliseconds to match lastPulledAt and timestamp.
     *
     * @param  array<int, Model>  $rows
     * @return list<array<string, mixed>>
     */
    private function records(array $rows): array
    {
        $records = [];

        foreach ($rows as $row) {
            $record = $row->toArray();
            unset($record['deleted_at']);

            foreach (array_keys($record) as $key) {
                $value = $row->getAttribute($key);

                if ($value instanceof DateTimeInterface) {
                    $record[$key] = (int) $value->format('Uv');
                }
            }

            $records[] = $record;
        }

        return $records;
    }
}
