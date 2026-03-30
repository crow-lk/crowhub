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
        Schema::table('terms_and_conditions', function (Blueprint $table) {
            $table->json('secondary_terms')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terms_and_conditions', function (Blueprint $table) {
            $table->dropColumn('secondary_terms');
        });
    }
};
