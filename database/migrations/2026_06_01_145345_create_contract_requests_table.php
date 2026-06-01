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
        Schema::create('contract_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->enum('sender_type', ['company', 'provider']);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // ex: esclarecimento, aditivo, renovacao, correcao, outro
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['pending', 'in_analysis', 'resolved', 'rejected'])->default('pending');
            $table->text('response_text')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_requests');
    }
};
