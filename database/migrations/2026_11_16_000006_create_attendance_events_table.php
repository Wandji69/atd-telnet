<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Terminal and PIN check-ins. Pushed by terminals with a client-chosen UUID.
     */
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('device_id')->nullable()->constrained();
            $table->foreignUuid('intern_id')->constrained();
            $table->string('kind');
            $table->string('status')->nullable();
            $table->timestamp('occurred_at');
            $table->string('photo_key')->nullable();
            $table->string('review_status')->default('none');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
