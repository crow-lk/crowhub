<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('category')->nullable()->index();
            $table->decimal('default_monthly_fee', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('maintenance_contract_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_service_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['maintenance_contract_id', 'maintenance_service_id']);
        });

        collect([
            ['Social Media Campaigns', 'Marketing'],
            ['Website Management', 'Web'],
            ['Website Content Updates', 'Web'],
            ['Website Hosting', 'Web'],
            ['Technical Support', 'Support'],
            ['SEO Maintenance', 'Marketing'],
            ['Analytics Reporting', 'Reporting'],
        ])->each(function (array $service): void {
            DB::table('maintenance_services')->insert([
                'name' => $service[0],
                'slug' => Str::slug($service[0]),
                'category' => $service[1],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_contract_service');
        Schema::dropIfExists('maintenance_services');
    }
};
