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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quote_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name')->index();
            $table->enum('status', ['active', 'paused', 'completed', 'cancelled'])->default('active')->index();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('github_project_url')->nullable();
            $table->string('github_repo_url')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
