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
        Schema::create('terms_and_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content');
            $table->string('number')->nullable(); // e.g., "1", "1.1", "1.2", "2", "2.1"
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('terms_and_conditions')
                ->onDelete('cascade');
        });

        // Pivot table for payment - terms_and_conditions relationship
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('terms_and_condition_id')
                ->constrained()
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['payment_id', 'terms_and_condition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_terms');
        Schema::dropIfExists('terms_and_conditions');
    }
};
