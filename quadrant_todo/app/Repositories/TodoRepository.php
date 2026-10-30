<?php

namespace App\Repositories;

use App\Enums\Quadrant;
use App\Models\Todo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

    //pagination rule
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

    //to PATCH a todo
    public function update(Todo $todo, array $changes): Todo
    {
        $todo->fill($changes);
        $todo->save();

        return $todo;
    }

    //to delete a todo
    public function delete(Todo $todo): void
    {
        $todo->delete();
    }


    //Carry-over of one todo: new date, carry_count + 1. One atomic UPDATE.
public function carryOver(Todo $todo, string $newDate): Todo
{
    Todo::where('id', $todo->id)
        ->where('user_id', $todo->user_id)
        ->update([
            'due_date' => $newDate,
            'carry_count' => DB::raw('carry_count + 1'),
        ]);

    return $todo->refresh();
}

// Postpone of one todo: new date, postpone_count + 1. carry_count is not touched.
public function postpone(Todo $todo, string $newDate): Todo
{
    Todo::where('id', $todo->id)
        ->where('user_id', $todo->user_id)
        ->update([
            'due_date' => $newDate,
            'postpone_count' => DB::raw('postpone_count + 1'),
        ]);

    return $todo->refresh();
}


/**
 * Bulk carry-over: every pending todo of this user due on $fromDate.
 * The whole move is one transaction: all todos move, or none do.
 */
public function carryOverDay(int $userId, string $fromDate, string $newDate): Collection
{
    return DB::transaction(function () use ($userId, $fromDate, $newDate) {
        // Select and lock the rows that match. pluck('id') returns just the ids.
        $ids = Todo::where('user_id', $userId)
            ->where('due_date', $fromDate)
            ->where('status', 'pending')
            ->lockForUpdate()
            ->pluck('id');

        if ($ids->isEmpty()) {
            return new Collection();
        }

        // The standard carry-over, applied to every selected todo in one statement.
        Todo::whereIn('id', $ids)->update([
            'due_date' => $newDate,
            'carry_count' => DB::raw('carry_count + 1'),
        ]);

        // Read them back by id. We cannot search by from_date any more: it just changed.
        return Todo::whereIn('id', $ids)
            ->orderByRaw('FIELD(quadrant, ?, ?, ?, ?)', Quadrant::values())
            ->orderBy('id')
            ->get();
    });
}
}