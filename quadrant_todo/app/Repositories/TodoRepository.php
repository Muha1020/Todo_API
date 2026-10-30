<?php

namespace App\Repositories;

use App\Enums\Quadrant;
use App\Models\Todo;
use Illuminate\Pagination\LengthAwarePaginator;

class TodoRepository
{
    public function create(int $userId, array $data): Todo
    {
        // The server's user_id goes last, so nothing can override it.
        return Todo::create(array_merge($data, ['user_id' => $userId]));
    }

    /** The ownership rule lives here: no user_id match, no todo. */
    public function findForUser(int $userId, int $id): ?Todo
    {
        return Todo::where('user_id', $userId)->where('id', $id)->first();
    }

    public function paginateForUser(int $userId, array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        $query = Todo::where('user_id', $userId);

        if (! empty($filters['quadrant'])) {
            $query->where('quadrant', $filters['quadrant']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['due_date'])) {
            $query->where('due_date', $filters['due_date']);
        }

        return $query
            ->orderBy('due_date')
            ->orderByRaw('FIELD(quadrant, ?, ?, ?, ?)', Quadrant::values())
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function update(Todo $todo, array $changes): Todo
    {
        $todo->fill($changes);
        $todo->save();

        return $todo;
    }

    public function delete(Todo $todo): void
    {
        $todo->delete();
    }
}