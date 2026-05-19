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
        Schema::create('project_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_no')->unique();
            $table->date('invoice_month');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft')->index();
            $table->date('issued_date');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });

        Schema::create('project_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_task_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_invoice_items');
        Schema::dropIfExists('project_invoices');
    }
};
