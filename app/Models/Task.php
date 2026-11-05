<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'name',
        'priority',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }

    /**
     * Get the project that owns the task.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope a query to order tasks by priority ascending.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Scope a query to filter by project id.
     */
    public function scopeForProject(Builder $query, ?int $projectId): Builder
    {
        if ($projectId === null) {
            return $query;
        }

        return $query->where('project_id', $projectId);
    }

    /**
     * Calculate the next available priority for a given project scope.
     */
    public static function nextPriority(?int $projectId = null): int
    {
        $max = static::query()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->max('priority');

        return ($max ?? 0) + 1;
    }
}
