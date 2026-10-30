<?php

namespace App\Services;

use App\Exceptions\ApiExceptions;
use App\Models\Todo;
use App\Repositories\TodoRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TodoService
{
    public function __construct(private TodoRepository $todos) {}

    public function create(int $userId, array $data): Todo
    {
        return $this->todos->create($userId, $data);
    }

    //to list all the todos
    public function list(int $userId, array $filters, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->todos->paginateForUser($userId, $filters, $page, $perPage);
    }

    public function get(int $userId, int $id): Todo
    {
        return $this->findOrFail($userId, $id);
    }

    public function update(int $userId, int $id, array $changes): Todo
    {
        $todo = $this->findOrFail($userId, $id);

        // The status changes: completed gets a time, pending clears it.
        if (array_key_exists('status', $changes) && $changes['status'] !== $todo->status) {
            $changes['completed_at'] = $changes['status'] === 'completed'
                ? now()->utc()
                : null;
        }

        return $this->todos->update($todo, $changes);
    }

    public function delete(int $userId, int $id): void
    {
        $this->todos->delete($this->findOrFail($userId, $id));
    }


    public function carryOver(int $userId, int $id, ?string $newDate): Todo
{
    $todo = $this->findOrFail($userId, $id);                 // step 1: absent gives 404

    if ($todo->status === 'completed') {                     // step 2: completed gives 409
        throw ApiExceptions::conflict('A completed todo cannot be carried over.');
    }

    $newDate ??= $this->today();                             // the default is today
    $this->assertNotBeforeToday($newDate);                   // step 3: before today gives 400

    return $this->todos->carryOver($todo, $newDate);         // step 4: update
}



public function carryOverDay(int $userId, string $fromDate, ?string $newDate): Collection
{
    $newDate ??= $this->today();
    $this->assertNotBeforeToday($newDate);

    return $this->todos->carryOverDay($userId, $fromDate, $newDate);
}


public function postpone(int $userId, int $id, string $newDate): Todo
{
    $todo = $this->findOrFail($userId, $id);                 // step 1: absent gives 404

    if ($todo->status === 'completed') {                     // step 2: completed gives 409
        throw ApiExceptions::conflict('A completed todo cannot be postponed.');
    }

    // Step 3: the date must be later than the current due date.
    if ($newDate <= $todo->due_date->toDateString()) {
        throw ApiExceptions::validation('The date is not valid.', [
            'new_due_date' => 'Use a date later than the current due date.',
        ]);
    }

    return $this->todos->postpone($todo, $newDate);          // step 4: update
}

/** Today in UTC, as YYYY-MM-DD. */
private function today(): string
{
    return now()->utc()->toDateString();
}

private function assertNotBeforeToday(string $date): void
{
    if ($date < $this->today()) {
        throw ApiExceptions::validation('The date is not valid.', [
            'new_due_date' => 'Use today or a later date.',
        ]);
    }
}

    /** One 404 for both "absent" and "belongs to another user". */
    private function findOrFail(int $userId, int $id): Todo
    {
        return $this->todos->findForUser($userId, $id)
            ?? throw ApiExceptions::notFound();
    }
}