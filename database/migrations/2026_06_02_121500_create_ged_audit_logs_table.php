<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ged_audit_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('contract_document_id')
                ->constrained('contract_documents')
                ->cascadeOnDelete();
            $blueprint->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $blueprint->string('action'); // 'approved', 'rejected'
            $blueprint->json('metadata')->nullable(); // For IP, User Agent, Rejection reason
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ged_audit_logs');
    }
};
