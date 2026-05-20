<?php

use App\Models\Client;
use App\Models\ClientJob;
use App\Models\Lead;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Client::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Lead::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(ClientJob::class)->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->enum('type', ['quote', 'project', 'campaign', 'maintenance', 'invoice', 'payment', 'note']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('activity_date')->index();
            $table->timestamps();

            $table->index(['client_id', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_activities');
    }
};
