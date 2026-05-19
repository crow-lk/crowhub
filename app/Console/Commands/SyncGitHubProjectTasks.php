<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\GitHub\GitHubProjectSync;
use Illuminate\Console\Command;

class SyncGitHubProjectTasks extends Command
{
    protected $signature = 'projects:sync-github {project? : Project ID to sync}';

    protected $description = 'Read GitHub Project items into CrowHub project tasks.';

    public function handle(GitHubProjectSync $sync): int
    {
        $projects = Project::query()
            ->when($this->argument('project'), fn ($query, $projectId) => $query->whereKey($projectId))
            ->whereNotNull('github_project_url')
            ->get();

        if ($projects->isEmpty()) {
            $this->warn('No projects with GitHub project URLs were found.');

            return self::SUCCESS;
        }

        foreach ($projects as $project) {
            try {
                $result = $sync->sync($project);

                $this->info(sprintf(
                    '%s: created %d, updated %d, skipped %d',
                    $project->name,
                    $result['created'],
                    $result['updated'],
                    $result['skipped'],
                ));
            } catch (\Throwable $exception) {
                $this->error($project->name.': '.$exception->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
