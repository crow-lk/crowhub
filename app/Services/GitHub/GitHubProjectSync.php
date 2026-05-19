<?php

namespace App\Services\GitHub;

use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class GitHubProjectSync
{
    public function __construct(protected HttpFactory $http) {}

    public function sync(Project $project): array
    {
        if (blank($project->github_project_url)) {
            throw new InvalidArgumentException('This project does not have a GitHub project URL.');
        }

        $token = config('services.github.token');

        if (blank($token)) {
            throw new RuntimeException('Missing GITHUB_PROJECTS_TOKEN or GITHUB_TOKEN.');
        }

        $source = $this->parseProjectUrl($project->github_project_url);
        $projectNodeId = $this->resolveProjectNodeId($source);

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($this->fetchProjectItems($projectNodeId) as $item) {
            $taskData = $this->mapItemToTaskData($item, $project);

            if (blank($taskData['title'])) {
                $skipped++;

                continue;
            }

            $task = ProjectTask::query()
                ->where('project_id', $project->id)
                ->where('github_item_id', $taskData['github_item_id'])
                ->first();

            if (! $task) {
                $project->tasks()->create([
                    ...$taskData,
                    'amount' => 0,
                ]);
                $created++;

                continue;
            }

            $task->fill($this->preserveBillingState($task, $taskData))->save();
            $updated++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    protected function parseProjectUrl(string $url): array
    {
        $parts = parse_url($url);
        $path = trim((string) ($parts['path'] ?? ''), '/');
        $segments = explode('/', $path);

        if (count($segments) >= 3 && $segments[0] === 'orgs' && $segments[2] === 'projects') {
            return [
                'type' => 'organization',
                'login' => $segments[1],
                'number' => (int) ($segments[3] ?? 0),
            ];
        }

        if (count($segments) >= 3 && $segments[0] === 'users' && $segments[2] === 'projects') {
            return [
                'type' => 'user',
                'login' => $segments[1],
                'number' => (int) ($segments[3] ?? 0),
            ];
        }

        throw new InvalidArgumentException('Use a GitHub Projects v2 URL like https://github.com/orgs/{org}/projects/{number}.');
    }

    protected function resolveProjectNodeId(array $source): string
    {
        $field = $source['type'] === 'organization' ? 'organization' : 'user';
        $query = <<<GRAPHQL
            query ResolveProject(\$login: String!, \$number: Int!) {
              {$field}(login: \$login) {
                projectV2(number: \$number) {
                  id
                }
              }
            }
            GRAPHQL;

        $data = $this->graphql($query, [
            'login' => $source['login'],
            'number' => $source['number'],
        ]);

        $id = Arr::get($data, "data.{$field}.projectV2.id");

        if (blank($id)) {
            throw new RuntimeException('GitHub project was not found or the token cannot access it.');
        }

        return $id;
    }

    protected function fetchProjectItems(string $projectNodeId): array
    {
        $items = [];
        $cursor = null;

        do {
            $query = <<<'GRAPHQL'
                query ProjectItems($projectId: ID!, $after: String) {
                  node(id: $projectId) {
                    ... on ProjectV2 {
                      items(first: 100, after: $after) {
                        pageInfo {
                          hasNextPage
                          endCursor
                        }
                        nodes {
                          id
                          updatedAt
                          fieldValues(first: 30) {
                            nodes {
                              ... on ProjectV2ItemFieldTextValue {
                                text
                                field {
                                  ... on ProjectV2FieldCommon {
                                    name
                                  }
                                }
                              }
                              ... on ProjectV2ItemFieldNumberValue {
                                number
                                field {
                                  ... on ProjectV2FieldCommon {
                                    name
                                  }
                                }
                              }
                              ... on ProjectV2ItemFieldDateValue {
                                date
                                field {
                                  ... on ProjectV2FieldCommon {
                                    name
                                  }
                                }
                              }
                              ... on ProjectV2ItemFieldSingleSelectValue {
                                name
                                field {
                                  ... on ProjectV2FieldCommon {
                                    name
                                  }
                                }
                              }
                              ... on ProjectV2ItemFieldIterationValue {
                                title
                                field {
                                  ... on ProjectV2FieldCommon {
                                    name
                                  }
                                }
                              }
                            }
                          }
                          content {
                            ... on Issue {
                              id
                              title
                              url
                              body
                              state
                              closedAt
                              assignees(first: 10) {
                                nodes {
                                  login
                                }
                              }
                            }
                            ... on PullRequest {
                              id
                              title
                              url
                              body
                              state
                              closedAt
                              assignees(first: 10) {
                                nodes {
                                  login
                                }
                              }
                            }
                            ... on DraftIssue {
                              id
                              title
                              body
                            }
                          }
                        }
                      }
                    }
                  }
                }
                GRAPHQL;

            $data = $this->graphql($query, [
                'projectId' => $projectNodeId,
                'after' => $cursor,
            ]);

            $connection = Arr::get($data, 'data.node.items', []);
            $items = array_merge($items, $connection['nodes'] ?? []);
            $cursor = Arr::get($connection, 'pageInfo.endCursor');
        } while (Arr::get($connection, 'pageInfo.hasNextPage', false));

        return $items;
    }

    protected function graphql(string $query, array $variables): array
    {
        $response = $this->http
            ->withToken(config('services.github.token'))
            ->acceptJson()
            ->post('https://api.github.com/graphql', [
                'query' => $query,
                'variables' => $variables,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('GitHub API request failed: '.$response->body());
        }

        $data = $response->json();

        if (! empty($data['errors'])) {
            throw new RuntimeException('GitHub API returned errors: '.json_encode($data['errors']));
        }

        return $data;
    }

    protected function mapItemToTaskData(array $item, Project $project): array
    {
        $content = $item['content'] ?? [];
        $fields = $this->fieldValuesByName($item);
        $githubStatus = $fields['status'] ?? null;
        $status = $this->mapStatus($githubStatus, $content['state'] ?? null);
        $closedAt = $content['closedAt'] ?? null;

        return [
            'project_id' => $project->id,
            'github_item_id' => $item['id'],
            'github_content_id' => $content['id'] ?? null,
            'github_task_url' => $content['url'] ?? $project->github_project_url,
            'github_status' => $githubStatus,
            'github_assignees' => collect(Arr::get($content, 'assignees.nodes', []))
                ->pluck('login')
                ->filter()
                ->values()
                ->all(),
            'github_synced_at' => now(),
            'title' => $content['title'] ?? 'GitHub project item '.$item['id'],
            'description' => $content['body'] ?? null,
            'status' => $status,
            'completed_at' => $status === 'done'
                ? ($closedAt ? Carbon::parse($closedAt) : now())
                : null,
        ];
    }

    protected function preserveBillingState(ProjectTask $task, array $data): array
    {
        if ($task->status === 'invoiced') {
            $data['status'] = 'invoiced';
            $data['completed_at'] = $task->completed_at;
            $data['invoiced_at'] = $task->invoiced_at;
        } elseif ($task->completed_at && $data['status'] === 'done') {
            $data['completed_at'] = $task->completed_at;
        }

        $data['amount'] = $task->amount;

        return $data;
    }

    protected function fieldValuesByName(array $item): array
    {
        $values = [];

        foreach (Arr::get($item, 'fieldValues.nodes', []) as $node) {
            $fieldName = Str::lower((string) Arr::get($node, 'field.name'));

            if (blank($fieldName)) {
                continue;
            }

            $values[$fieldName] = $node['name']
                ?? $node['text']
                ?? $node['number']
                ?? $node['date']
                ?? $node['title']
                ?? null;
        }

        return $values;
    }

    protected function mapStatus(?string $githubStatus, ?string $contentState): string
    {
        $value = Str::lower(trim((string) ($githubStatus ?: $contentState)));

        return match (true) {
            Str::contains($value, ['done', 'complete', 'completed', 'closed', 'ready for invoice']) => 'done',
            Str::contains($value, ['progress', 'doing', 'active']) => 'in_progress',
            Str::contains($value, ['cancel', 'reject']) => 'cancelled',
            default => 'todo',
        };
    }
}
