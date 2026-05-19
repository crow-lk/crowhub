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
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->string('github_item_id')->nullable()->unique()->after('project_id');
            $table->string('github_content_id')->nullable()->after('github_item_id');
            $table->string('github_status')->nullable()->after('github_task_url');
            $table->json('github_assignees')->nullable()->after('github_status');
            $table->timestamp('github_synced_at')->nullable()->after('github_assignees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'github_item_id',
                'github_content_id',
                'github_status',
                'github_assignees',
                'github_synced_at',
            ]);
        });
    }
};
