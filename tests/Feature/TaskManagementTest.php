<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_tasks_dashboard(): void
    {
        $project = Project::factory()->create(['name' => 'Demo Project']);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'name' => 'Review PR #42',
            'priority' => 1,
        ]);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertSee('Review PR #42');
        $response->assertSee('Demo Project');
        $response->assertSee('#1');
    }

    public function test_can_create_a_task(): void
    {
        $project = Project::factory()->create();

        $response = $this->post(route('tasks.store'), [
            'name' => 'Setup CI pipeline',
            'project_id' => $project->id,
            'priority' => 1,
        ]);

        $response->assertRedirect(route('tasks.index', ['project_id' => $project->id]));
        $this->assertDatabaseHas('tasks', [
            'name' => 'Setup CI pipeline',
            'project_id' => $project->id,
            'priority' => 1,
        ]);

        $task = Task::where('name', 'Setup CI pipeline')->first();
        $this->assertNotNull($task->created_at);
        $this->assertNotNull($task->updated_at);
    }

    public function test_automatically_assigns_sequential_priority_when_omitted(): void
    {
        $project = Project::factory()->create();

        $task1 = Task::factory()->create(['project_id' => $project->id, 'priority' => 1]);
        $task2 = Task::factory()->create(['project_id' => $project->id, 'priority' => 2]);

        $this->post(route('tasks.store'), [
            'name' => 'Third task in order',
            'project_id' => $project->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'name' => 'Third task in order',
            'priority' => 3,
        ]);
    }

    public function test_can_view_edit_task_page(): void
    {
        $task = Task::factory()->create(['name' => 'Original Task']);

        $response = $this->get(route('tasks.edit', $task));

        $response->assertStatus(200);
        $response->assertSee('Original Task');
    }

    public function test_can_update_a_task(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->create([
            'name' => 'Old Title',
            'priority' => 2,
        ]);

        $response = $this->put(route('tasks.update', $task), [
            'name' => 'Updated Task Title',
            'project_id' => $project->id,
            'priority' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'name' => 'Updated Task Title',
            'project_id' => $project->id,
            'priority' => 1,
        ]);
    }

    public function test_can_delete_a_task_and_recompact_priorities(): void
    {
        $project = Project::factory()->create();

        $task1 = Task::factory()->create(['project_id' => $project->id, 'priority' => 1]);
        $task2 = Task::factory()->create(['project_id' => $project->id, 'priority' => 2]);
        $task3 = Task::factory()->create(['project_id' => $project->id, 'priority' => 3]);

        $response = $this->delete(route('tasks.destroy', $task2));

        $response->assertRedirect();
        $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);

        $this->assertEquals(1, $task1->fresh()->priority);
        $this->assertEquals(2, $task3->fresh()->priority);
    }

    public function test_can_reorder_tasks_via_drag_and_drop_endpoint(): void
    {
        $project = Project::factory()->create();

        $taskA = Task::factory()->create(['name' => 'Alpha', 'project_id' => $project->id, 'priority' => 1]);
        $taskB = Task::factory()->create(['name' => 'Beta', 'project_id' => $project->id, 'priority' => 2]);
        $taskC = Task::factory()->create(['name' => 'Gamma', 'project_id' => $project->id, 'priority' => 3]);

        $response = $this->postJson(route('tasks.reorder'), [
            'task_ids' => [$taskC->id, $taskA->id, $taskB->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'count' => 3,
        ]);

        $this->assertEquals(1, $taskC->fresh()->priority);
        $this->assertEquals(2, $taskA->fresh()->priority);
        $this->assertEquals(3, $taskB->fresh()->priority);
    }

    public function test_can_filter_tasks_by_project(): void
    {
        $projectA = Project::factory()->create(['name' => 'Alpha Project']);
        $projectB = Project::factory()->create(['name' => 'Beta Project']);

        $taskA = Task::factory()->create(['name' => 'Alpha Task Unique', 'project_id' => $projectA->id]);
        $taskB = Task::factory()->create(['name' => 'Beta Task Unique', 'project_id' => $projectB->id]);

        $responseA = $this->get(route('tasks.index', ['project_id' => $projectA->id]));
        $responseA->assertStatus(200);
        $responseA->assertSee('Alpha Task Unique');
        $responseA->assertDontSee('Beta Task Unique');

        $responseB = $this->get(route('tasks.index', ['project_id' => $projectB->id]));
        $responseB->assertStatus(200);
        $responseB->assertSee('Beta Task Unique');
        $responseB->assertDontSee('Alpha Task Unique');
    }

    public function test_can_create_a_project(): void
    {
        $response = $this->post(route('projects.store'), [
            'name' => 'Mobile Re-architecture',
            'description' => 'Migrate hybrid app to native SwiftUI.',
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Mobile Re-architecture',
            'description' => 'Migrate hybrid app to native SwiftUI.',
        ]);
    }
}
