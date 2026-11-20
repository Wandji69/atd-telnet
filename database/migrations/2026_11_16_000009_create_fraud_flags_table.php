<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Face-match results. Server only. The scorer is `stub` in the prototype.
     */
    public function up(): void
    {
        Schema::create('fraud_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('intern_id')->constrained();
            $table->foreignUuid('evidence_id')->nullable()->constrained('evidence');
            $table->foreignUuid('attendance_event_id')->nullable()->constrained();
            $table->foreignUuid('remote_check_in_id')->nullable()->constrained();
            $table->decimal('score', 5, 2);
            $table->string('scorer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_flags');
    }
};
