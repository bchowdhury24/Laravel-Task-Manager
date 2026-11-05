<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks, optionally filtered by project.
     */
    public function index(Request $request): View
    {
        $selectedProjectId = $request->filled('project_id')
            ? (int) $request->query('project_id')
            : null;

        $projects = Project::withCount('tasks')
            ->orderBy('name')
            ->get();

        $tasksQuery = Task::with('project')->ordered();

        if ($selectedProjectId !== null) {
            $tasksQuery->where('project_id', $selectedProjectId);
        }

        $tasks = $tasksQuery->get();

        $currentProject = $selectedProjectId ? Project::find($selectedProjectId) : null;

        return view('tasks.index', compact('tasks', 'projects', 'selectedProjectId', 'currentProject'));
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $projectId = !empty($data['project_id']) ? (int) $data['project_id'] : null;

        if (!empty($data['priority'])) {
            $targetPriority = (int) $data['priority'];
            Task::query()
                ->when($projectId, fn ($q) => $q->where('project_id', $projectId), fn ($q) => $q->whereNull('project_id'))
                ->where('priority', '>=', $targetPriority)
                ->increment('priority');
            $data['priority'] = $targetPriority;
        } else {
            $data['priority'] = Task::nextPriority($projectId);
        }

        $task = Task::create($data);

        return redirect()
            ->route('tasks.index', $projectId ? ['project_id' => $projectId] : [])
            ->with('success', 'Task created successfully.');
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task): View|JsonResponse
    {
        if (request()->wantsJson()) {
            return response()->json([
                'task' => $task->load('project'),
            ]);
        }

        $projects = Project::orderBy('name')->get();

        return view('tasks.edit', compact('task', 'projects'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $newProjectId = !empty($data['project_id']) ? (int) $data['project_id'] : null;
        $oldProjectId = $task->project_id;

        if ($newProjectId !== $oldProjectId && empty($data['priority'])) {
            $data['priority'] = Task::nextPriority($newProjectId);
        }

        $task->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully.',
                'task' => $task->load('project'),
            ]);
        }

        return redirect()
            ->route('tasks.index', $task->project_id ? ['project_id' => $task->project_id] : [])
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): RedirectResponse|JsonResponse
    {
        $projectId = $task->project_id;
        $deletedPriority = $task->priority;

        $task->delete();

        Task::query()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId), fn ($q) => $q->whereNull('project_id'))
            ->where('priority', '>', $deletedPriority)
            ->decrement('priority');

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully.',
            ]);
        }

        return redirect()
            ->route('tasks.index', $projectId ? ['project_id' => $projectId] : [])
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Reorder tasks and update priorities.
     */
    public function reorder(ReorderTasksRequest $request): JsonResponse
    {
        $taskIds = $request->validated('task_ids');

        DB::transaction(function () use ($taskIds) {
            foreach ($taskIds as $index => $id) {
                Task::where('id', $id)->update(['priority' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Task priorities updated successfully.',
            'count' => count($taskIds),
        ]);
    }
}
