<?php

namespace App\Observers;

use App\Models\ClientActivity;
use App\Models\Project;
use App\Services\ClientJobSync;

class ProjectObserver
{
    public function saved(Project $project): void
    {
        app(ClientJobSync::class)->sync($project);

        if ($project->wasChanged('status')) {
            ClientActivity::recordFor($project, 'project', 'Project '.$project->name.' marked '.str_replace('_', ' ', $project->status));
        }
    }
}
