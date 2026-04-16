<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons_learned', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('issue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('title', 255);
            $table->string('category', 60);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');

            $table->text('problem_summary');
            $table->text('root_cause');
            $table->text('corrective_action');
            $table->text('preventive_action')->nullable();
            $table->text('recommendation')->nullable();

            $table->json('tags')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->boolean('is_published')->default(true);

            $table->timestamps();

            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'severity']);
            $table->index(['project_id']);
            $table->index(['issue_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons_learned');
    }
};
