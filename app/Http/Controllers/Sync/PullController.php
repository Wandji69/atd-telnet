<?php

namespace App\Http\Controllers\Sync;

use App\Actions\Sync\PullChanges;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /api/sync/pull — the WatermelonDB pull endpoint.
 */
class PullController extends Controller
{
    public function __invoke(Request $request, PullChanges $pullChanges): JsonResponse
    {
        $validated = $request->validate([
            'lastPulledAt' => ['present', 'nullable', 'integer', 'min:0'],
            'device_id' => ['nullable', 'uuid'],
        ]);

        $lastPulledAt = $validated['lastPulledAt'] ?? null;
        $deviceId = $validated['device_id'] ?? null;

        return response()->json($pullChanges(
            $lastPulledAt === null ? null : (int) $lastPulledAt,
            is_string($deviceId) ? $deviceId : null,
        ));
    }
}
