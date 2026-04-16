<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'semi_annual', 'annual'])->default('monthly');
            $table->enum('trigger_type', ['calendar', 'runtime_hours', 'condition'])->default('calendar');
            $table->integer('runtime_hours_interval')->nullable();
            $table->date('next_due_date')->nullable();
            $table->date('last_completed_date')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->json('checklist')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'next_due_date']);
            $table->index(['asset_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
