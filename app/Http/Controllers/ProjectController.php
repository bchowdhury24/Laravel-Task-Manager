<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProjectController extends Controller
{
    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse|JsonResponse
    {
        $project = Project::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Project '{$project->name}' created successfully.",
                'project' => $project,
            ], 201);
        }

        return redirect()
            ->route('tasks.index', ['project_id' => $project->id])
            ->with('success', "Project '{$project->name}' created successfully.");
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project): RedirectResponse|JsonResponse
    {
        $name = $project->name;
        $project->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Project '{$name}' deleted successfully.",
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', "Project '{$name}' deleted successfully.");
    }
}
