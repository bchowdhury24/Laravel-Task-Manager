@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <label for="project-filter" class="text-sm font-semibold text-slate-700 flex items-center space-x-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span>Filter Project:</span>
            </label>
            <div class="relative min-w-[220px]">
                <select id="project-filter" onchange="filterByProject(this.value)" class="w-full pl-3 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all cursor-pointer">
                    <option value="" {{ empty($selectedProjectId) ? 'selected' : '' }}>All Projects ({{ $projects->sum('tasks_count') }})</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ (string)$selectedProjectId === (string)$project->id ? 'selected' : '' }}>
                            {{ $project->name }} ({{ $project->tasks_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="button" onclick="openModal('create-project-modal')" class="inline-flex items-center px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium shadow-sm transition-all hover:border-slate-300">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Project
            </button>

            @if($currentProject)
                <form action="{{ route('projects.destroy', $currentProject) }}" method="POST" onsubmit="return confirm('Delete project &quot;{{ $currentProject->name }}&quot; and all its tasks?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-2.5 rounded-xl text-rose-600 hover:text-rose-700 hover:bg-rose-50 text-xs font-semibold transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Delete Project
                    </button>
                </form>
            @endif
        </div>

        <div>
            <button type="button" onclick="openModal('create-task-modal')" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm hover:shadow-brand-500/25 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create Task
            </button>
        </div>
    </div>

    @if ($currentProject)
        <div class="px-5 py-3.5 bg-indigo-50/70 border border-indigo-100 rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                <span class="text-sm font-semibold text-slate-900">Project: {{ $currentProject->name }}</span>
                @if($currentProject->description)
                    <span class="text-xs text-slate-500 hidden sm:inline">&mdash; {{ $currentProject->description }}</span>
                @endif
            </div>
            <a href="{{ route('tasks.index') }}" class="text-xs font-medium text-brand-600 hover:text-brand-800">Clear filter</a>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Tasks</h2>
                <p class="text-xs text-slate-500 mt-0.5">Drag tasks to reorder priority.</p>
            </div>
            <span id="task-counter" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                {{ $tasks->count() }} {{ Str::plural('task', $tasks->count()) }}
            </span>
        </div>

        @if($tasks->isEmpty())
            <div class="py-16 px-4 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-800">No tasks found</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    @if($currentProject)
                        There are no tasks assigned to "{{ $currentProject->name }}" yet.
                    @else
                        Your task list is empty. Create your first task to get started!
                    @endif
                </p>
                <button type="button" onclick="openModal('create-task-modal')" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition-all">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Task
                </button>
            </div>
        @else
            <ul id="task-list" class="divide-y divide-slate-100">
                @foreach ($tasks as $task)
                    <li data-id="{{ $task->id }}" class="task-item flex items-center justify-between p-4 sm:px-6 hover:bg-slate-50/70 transition-colors duration-150 group">
                        <div class="flex items-center space-x-3 sm:space-x-4 min-w-0 flex-1">
                            <button type="button" class="drag-handle cursor-grab active:cursor-grabbing p-1.5 text-slate-300 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors focus:outline-none" title="Drag to reorder priority">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                </svg>
                            </button>

                            <div class="priority-badge flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 text-brand-700 font-bold text-xs tracking-tight" data-priority="{{ $task->priority }}">
                                #{{ $task->priority }}
                            </div>

                            <div class="min-w-0 flex-1 pr-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900 task-name-text break-words">
                                        {{ $task->name }}
                                    </span>

                                    @if ($task->project)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-500 mr-1.5"></span>
                                            {{ $task->project->name }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center space-x-3 mt-1 text-xs text-slate-400">
                                    <span title="Created at: {{ $task->created_at->format('M d, Y H:i:s') }}">
                                        Created {{ $task->created_at->diffForHumans() }}
                                    </span>
                                    @if($task->updated_at->gt($task->created_at))
                                        <span>&bull;</span>
                                        <span title="Updated at: {{ $task->updated_at->format('M d, Y H:i:s') }}">
                                            Edited {{ $task->updated_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-1 sm:space-x-2 flex-shrink-0">
                            <button type="button" 
                                onclick="openEditModal({{ $task->id }}, @js($task->name), {{ $task->project_id ?? 'null' }}, {{ $task->priority }})" 
                                class="p-2 text-slate-400 hover:text-brand-600 hover:bg-indigo-50 rounded-lg transition-colors" 
                                title="Edit task">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete task &quot;{{ addslashes($task->name) }}&quot;?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete task">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<div id="create-task-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transition-all">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                <span>Create New Task</span>
            </h3>
            <button type="button" onclick="closeModal('create-task-modal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('tasks.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="create-task-name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Task Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="create-task-name" name="name" required placeholder="Task title" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            <div>
                <label for="create-task-project" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Assign Project (Optional)
                </label>
                <select id="create-task-project" name="project_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                    <option value="">-- No Project --</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" {{ (string)$selectedProjectId === (string)$project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="create-task-priority" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Priority (Optional)
                </label>
                <input type="number" id="create-task-priority" name="priority" min="1" placeholder="Leave empty for auto priority" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                <p class="text-xs text-slate-400 mt-1">Leave empty to append to the end of the list.</p>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('create-task-modal')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm">Save Task</button>
            </div>
        </form>
    </div>
</div>

<div id="edit-task-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transition-all">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span>Edit Task</span>
            </h3>
            <button type="button" onclick="closeModal('edit-task-modal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="edit-task-form" method="POST" class="mt-5 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit-task-name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Task Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="edit-task-name" name="name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            <div>
                <label for="edit-task-project" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Project
                </label>
                <select id="edit-task-project" name="project_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 bg-white">
                    <option value="">-- No Project --</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="edit-task-priority" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Priority
                </label>
                <input type="number" id="edit-task-priority" name="priority" min="1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('edit-task-modal')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm">Update Task</button>
            </div>
        </form>
    </div>
</div>

<div id="create-project-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 transition-all">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900 flex items-center space-x-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Create New Project</span>
            </h3>
            <button type="button" onclick="closeModal('create-project-modal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('projects.store') }}" method="POST" class="mt-5 space-y-4">
            @csrf

            <div>
                <label for="create-project-name" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Project Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="create-project-name" name="name" required placeholder="Project name" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>

            <div>
                <label for="create-project-desc" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                    Description (Optional)
                </label>
                <textarea id="create-project-desc" name="description" rows="3" placeholder="Brief project description" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('create-project-modal')" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-medium">Cancel</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm">Create Project</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function filterByProject(projectId) {
        const url = new URL(window.location.href);
        if (projectId) {
            url.searchParams.set('project_id', projectId);
        } else {
            url.searchParams.delete('project_id');
        }
        window.location.href = url.toString();
    }

    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('hidden');
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) setTimeout(() => firstInput.focus(), 50);
        }
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[id$="-modal"]').forEach(modal => modal.classList.add('hidden'));
        }
    });

    document.querySelectorAll('[id$="-modal"]').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    });

    function openEditModal(taskId, name, projectId, priority) {
        const form = document.getElementById('edit-task-form');
        form.action = `/tasks/${taskId}`;
        document.getElementById('edit-task-name').value = name;
        document.getElementById('edit-task-project').value = projectId || '';
        document.getElementById('edit-task-priority').value = priority;

        openModal('edit-task-modal');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const listEl = document.getElementById('task-list');
        if (!listEl) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        new Sortable(listEl, {
            handle: '.drag-handle',
            animation: 180,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: function () {
                const items = listEl.querySelectorAll('li.task-item');
                const taskIds = [];

                items.forEach((item, index) => {
                    const newPriority = index + 1;
                    const badge = item.querySelector('.priority-badge');
                    if (badge) {
                        badge.textContent = `#${newPriority}`;
                        badge.setAttribute('data-priority', newPriority);
                    }
                    taskIds.push(parseInt(item.getAttribute('data-id'), 10));
                });

                fetch("{{ route('tasks.reorder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ task_ids: taskIds }),
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to update priority order.');
                    }
                    showToast('Task order updated.', 'success');
                })
                .catch(error => {
                    console.error('Error reordering tasks:', error);
                    showToast(error.message || 'Error updating task order.', 'error');
                });
            }
        });
    });
</script>
@endpush
