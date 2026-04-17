<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('external_issue_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'open', 'in_progress', 'work_completed', 'closed', 'deferred'])->default('open');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->string('issue_type')->nullable();
            $table->string('source_system')->default('external');
            $table->string('source_id')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'external_issue_id']);
            $table->index(['project_id', 'status']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
