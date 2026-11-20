<?php

namespace App\Models;

use App\Enums\CardStatus;
use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * An intern's NFC card state. Pulled by terminals. Holds no key material.
 *
 * @property string $id
 * @property string $intern_id
 * @property CardStatus $status
 * @property int $offline_log_count
 * @property Carbon|null $last_status_at
 * @property int $token_version
 * @property Carbon|null $revoked_at
 * @property bool $needs_reissue
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'id',
    'intern_id',
    'status',
    'offline_log_count',
    'last_status_at',
    'token_version',
    'revoked_at',
    'needs_reissue',
])]
class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Matches the column defaults, so a new model reports them before a refresh.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'CHECKED_OUT',
        'offline_log_count' => 0,
        'token_version' => 1,
        'needs_reissue' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CardStatus::class,
            'offline_log_count' => 'integer',
            'last_status_at' => 'datetime',
            'token_version' => 'integer',
            'revoked_at' => 'datetime',
            'needs_reissue' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Intern, $this>
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(Intern::class);
    }
}
