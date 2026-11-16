<?php

namespace App\Models;

use Database\Factories\FraudFlagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A face-match result. Server only. The scorer is `stub` in the prototype.
 *
 * @property string $id
 * @property string $intern_id
 * @property string|null $evidence_id
 * @property string|null $attendance_event_id
 * @property string|null $remote_check_in_id
 * @property float $score
 * @property string $scorer
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'intern_id',
    'evidence_id',
    'attendance_event_id',
    'remote_check_in_id',
    'score',
    'scorer',
])]
class FraudFlag extends Model
{
    /** @use HasFactory<FraudFlagFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'float',
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
     * @return BelongsTo<Evidence, $this>
     */
    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class);
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

    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
