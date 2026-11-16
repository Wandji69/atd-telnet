<?php

namespace App\Models;

use Database\Factories\EvidenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Photo metadata. Pushed by terminals. The file bytes live on the evidence
 * disk under object_key, never in the database.
 *
 * @property string $id
 * @property string $intern_id
 * @property string|null $attendance_event_id
 * @property string|null $remote_check_in_id
 * @property string $object_key
 * @property Carbon|null $captured_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'id',
    'intern_id',
    'attendance_event_id',
    'remote_check_in_id',
    'object_key',
    'captured_at',
])]
class Evidence extends Model
{
    /** @use HasFactory<EvidenceFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'evidence';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Intern, $this>
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }

    /**
     * @return BelongsTo<AttendanceEvent, $this>
     */
    public function attendanceEvent(): BelongsTo
    {
        return $this->belongsTo(AttendanceEvent::class);
    }

    /**
     * @return BelongsTo<RemoteCheckIn, $this>
     */
    public function remoteCheckIn(): BelongsTo
    {
        return $this->belongsTo(RemoteCheckIn::class);
    }
}
