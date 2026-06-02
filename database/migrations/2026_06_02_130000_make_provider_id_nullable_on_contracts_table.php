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
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id')->nullable()->change();
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_id')->nullable(false)->change();
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
        });
    }
};
