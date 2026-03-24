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
        // Pivot table for quote - terms_and_conditions relationship
        Schema::create('quote_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('terms_and_condition_id')
                ->constrained()
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['quote_id', 'terms_and_condition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_terms');
    }
};
