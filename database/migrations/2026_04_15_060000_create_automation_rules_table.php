<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('trigger_type', [
                'work_order_created',
                'work_order_status_changed',
                'sla_approaching',
                'sla_breached',
                'sensor_alert',
                'issue_imported',
                'pm_due',
            ]);
            $table->json('conditions');
            $table->json('actions');
            $table->unsignedInteger('execution_count')->default(0);
            $table->timestamp('last_executed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'trigger_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
