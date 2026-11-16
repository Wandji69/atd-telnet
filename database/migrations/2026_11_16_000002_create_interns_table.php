<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Intern profiles. Pulled by terminals. The PIN is stored only as a hash,
     * and the reference photo bytes live on the evidence disk, not here.
     */
    public function up(): void
    {
        Schema::create('interns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('pin_hash')->nullable();
            $table->string('reference_photo_key')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interns');
    }
};
