<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('source_system');
            $table->string('source_entity');
            $table->string('source_status');
            $table->string('target_entity');
            $table->string('target_status');
            $table->boolean('auto_transition')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'source_system', 'source_entity', 'source_status', 'target_entity'], 'status_mappings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_mappings');
    }
};
