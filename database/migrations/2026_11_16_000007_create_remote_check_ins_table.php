<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Off-site check-ins with GPS, a cell-tower reference, and a selfie key.
     * Pushed by terminals. The server fills site_id and distance_meters (T9).
     */
    public function up(): void
    {
        Schema::create('remote_check_ins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('device_id')->nullable()->constrained();
            $table->foreignUuid('intern_id')->constrained();
            $table->foreignUuid('site_id')->nullable()->constrained();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('cell_tower_ref')->nullable();
            $table->timestamp('occurred_at');
            $table->string('photo_key')->nullable();
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->string('review_status')->default('none');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remote_check_ins');
    }
};
