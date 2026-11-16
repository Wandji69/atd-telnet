<?php

namespace App\Models;

use App\Enums\ReviewAction;
use Database\Factories\ReviewDecisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A manager's override or dismissal of a review item. Server only.
 *
 * @property string $id
 * @property string $user_id
 * @property string|null $attendance_event_id
 * @property string|null $remote_check_in_id
 * @property ReviewAction $action
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'attendance_event_id',
    'remote_check_in_id',
    'action',
    'reason',
])]
class ReviewDecision extends Model
{
    /** @use HasFactory<ReviewDecisionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'action' => ReviewAction::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
