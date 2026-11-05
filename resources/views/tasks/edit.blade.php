@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-4">
        <a href="{{ route('tasks.index', $task->project_id ? ['project_id' => $task->project_id] : []) }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to tasks
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h1 class="text-xl font-bold text-slate-900">Edit Task</h1>
            <p class="text-xs text-slate-500 mt-1">Update task details and priority.</p>
        </div>

        <form action="{{ route('tasks.update', $task) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Task Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $task->name) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            <div>
                <label for="project_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Project
                </label>
                <select id="project_id" name="project_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                    <option value="">-- No Project --</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ (string)old('project_id', $task->project_id) === (string)$project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="priority" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Priority
                </label>
                <input type="number" id="priority" name="priority" min="1" value="{{ old('priority', $task->priority) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <p class="text-xs text-slate-400 mt-1">Lower number means higher priority.</p>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                <a href="{{ route('tasks.index', $task->project_id ? ['project_id' => $task->project_id] : []) }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-medium">Cancel</a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
