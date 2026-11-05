<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderTasksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Support either `task_ids: [1, 2, 3]` or `tasks: [1, 2, 3]` or `tasks: [['id' => 1], ...]`
        $ids = $this->input('task_ids') ?? $this->input('tasks') ?? $this->input('order');

        if (is_array($ids)) {
            $normalized = [];
            foreach ($ids as $item) {
                if (is_array($item) && isset($item['id'])) {
                    $normalized[] = (int) $item['id'];
                } elseif (is_numeric($item)) {
                    $normalized[] = (int) $item;
                }
            }
            $this->merge(['task_ids' => $normalized]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer', 'exists:tasks,id'],
        ];
    }
}
