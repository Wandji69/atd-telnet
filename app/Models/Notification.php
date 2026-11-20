<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A stored alert dispatch. Server only. The prototype never sends it to an
 * external provider; the channel is `log`.
 *
 * @property string $id
 * @property string|null $fraud_flag_id
 * @property string $channel
 * @property array<string, mixed> $payload
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['fraud_flag_id', 'channel', 'payload', 'status'])]
class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    /**
     * @return BelongsTo<FraudFlag, $this>
     */
    public function fraudFlag(): BelongsTo
    {
        return $this->belongsTo(FraudFlag::class);
    }
}
