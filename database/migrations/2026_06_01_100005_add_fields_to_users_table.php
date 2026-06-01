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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->after('password')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreignId('provider_id')->after('company_id')->nullable()->constrained('providers')->onDelete('set null');
            $table->enum('role', ['super_admin', 'gestor', 'fornecedor'])->after('provider_id')->default('fornecedor');
            $table->boolean('active')->after('role')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
            $table->dropColumn('role');
            $table->dropColumn('active');
        });
    }
};
