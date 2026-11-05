<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $projects = [
            [
                'name' => 'Website Redesign',
                'description' => 'Revamping the company marketing portal and client dashboard.',
                'tasks' => [
                    'Gather brand guidelines & client moodboards',
                    'Design responsive wireframes in Figma',
                    'Conduct stakeholder usability review',
                    'Implement modern responsive layouts and themes',
                    'Audit SEO meta tags, performance and accessibility',
                ],
            ],
            [
                'name' => 'Mobile App Launch',
                'description' => 'Preparing iOS and Android apps for App Store submission.',
                'tasks' => [
                    'Configure push notifications & deep-links',
                    'Implement biometric authentication flow',
                    'Setup end-to-end automated UI test suite',
                    'Draft privacy policy and App Store listing copies',
                    'Conduct final beta test with internal users',
                ],
            ],
            [
                'name' => 'Infrastructure & Security',
                'description' => 'Hardening server infrastructure, CI/CD pipelines and database backups.',
                'tasks' => [
                    'Upgrade server packages and runtime dependencies',
                    'Configure daily automated MySQL encrypted backups',
                    'Set up Redis caching and rate-limiting rules',
                    'Deploy SSL/TLS certificates and automated renewals',
                ],
            ],
        ];

        foreach ($projects as $projData) {
            $tasks = $projData['tasks'];
            unset($projData['tasks']);

            $project = Project::create($projData);

            foreach ($tasks as $index => $taskName) {
                Task::create([
                    'project_id' => $project->id,
                    'name' => $taskName,
                    'priority' => $index + 1,
                ]);
            }
        }
    }
}
