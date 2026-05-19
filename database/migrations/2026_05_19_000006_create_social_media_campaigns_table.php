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
        Schema::create('social_media_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_contract_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('status', ['planned', 'active', 'completed', 'paused', 'cancelled'])->default('planned')->index();
            $table->json('platforms')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('objective')->nullable();
            $table->text('content_plan')->nullable();
            $table->text('results_summary')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['maintenance_contract_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_campaigns');
    }
};
