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
        Schema::table('contract_requests', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('description');
            $table->string('original_name')->nullable()->after('file_path');
            $table->string('response_file_path')->nullable()->after('response_text');
            $table->string('response_original_name')->nullable()->after('response_file_path');
            $table->date('due_date')->nullable()->after('response_original_name');
            $table->boolean('requires_attachment')->default(false)->after('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract_requests', function (Blueprint $table) {
            $table->dropColumn([
                'file_path',
                'original_name',
                'response_file_path',
                'response_original_name',
                'due_date',
                'requires_attachment'
            ]);
        });
    }
};
