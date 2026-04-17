<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('external_project_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['planning', 'commissioning', 'closeout', 'operational', 'archived'])->default('commissioning');
            $table->string('project_type')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->decimal('readiness_score', 5, 2)->default(0);
            $table->integer('total_issues')->default(0);
            $table->integer('open_issues')->default(0);
            $table->integer('total_tests')->default(0);
            $table->integer('completed_tests')->default(0);
            $table->integer('total_closeout_docs')->default(0);
            $table->integer('completed_closeout_docs')->default(0);
            $table->date('target_handover_date')->nullable();
            $table->date('actual_handover_date')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'external_project_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
