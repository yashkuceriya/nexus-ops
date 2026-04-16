<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_signoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();

            // Role the approver signed in — e.g. "commissioning_authority",
            // "owner_rep", "facility_manager".
            $table->string('signer_role', 60);

            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])
                ->default('pending');

            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Signature artifact. We store both the hash (for tamper detection)
            // and the optional rendered signature image (data-URI), allowing
            // the audit system to verify the signoff without storing raw PII.
            $table->string('signature_hash', 128)->nullable();
            $table->mediumText('signature_image')->nullable();
            $table->ipAddress('signature_ip')->nullable();
            $table->string('signature_user_agent', 500)->nullable();

            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['project_id', 'status']);
            $table->index(['asset_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_signoffs');
    }
};
