<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Photo metadata. Pushed by terminals. The file bytes live on the
     * evidence disk under object_key, never in the database.
     */
    public function up(): void
    {
        Schema::create('evidence', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('intern_id')->constrained();
            $table->foreignUuid('attendance_event_id')->nullable()->constrained();
            $table->foreignUuid('remote_check_in_id')->nullable()->constrained();
            $table->string('object_key');
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence');
    }
};
