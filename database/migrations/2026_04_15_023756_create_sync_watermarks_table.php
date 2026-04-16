<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_watermarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('connector');
            $table->string('entity');
            $table->string('cursor')->nullable();
            $table->timestamp('last_successful_sync_at')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->string('last_error')->nullable();
            $table->integer('consecutive_failures')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'connector', 'entity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_watermarks');
    }
};
