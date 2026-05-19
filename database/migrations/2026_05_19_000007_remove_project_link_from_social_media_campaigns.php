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
        if (! Schema::hasColumn('social_media_campaigns', 'project_id')) {
            return;
        }

        Schema::table('social_media_campaigns', function (Blueprint $table) {
            $table->dropIndex('social_media_campaigns_project_id_status_index');
            $table->dropConstrainedForeignId('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('social_media_campaigns', 'project_id')) {
            return;
        }

        Schema::table('social_media_campaigns', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('lead_id')->constrained()->nullOnDelete();
            $table->index(['project_id', 'status']);
        });
    }
};
