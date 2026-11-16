<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An intern's NFC card state. Pulled by terminals.
     * No AES key material is stored here.
     */
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('intern_id')->constrained();
            $table->string('status')->default('CHECKED_OUT');
            $table->unsignedInteger('offline_log_count')->default(0);
            $table->timestamp('last_status_at')->nullable();
            $table->unsignedInteger('token_version')->default(1);
            $table->timestamp('revoked_at')->nullable();
            $table->boolean('needs_reissue')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
