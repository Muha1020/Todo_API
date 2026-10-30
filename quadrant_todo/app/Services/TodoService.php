<?php

namespace App\Services;

use App\Exceptions\ApiExceptions;
use App\Models\Todo;
use App\Repositories\TodoRepository;
use Illuminate\Pagination\LengthAwarePaginator;

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

    /** One 404 for both "absent" and "belongs to another user". */
    private function findOrFail(int $userId, int $id): Todo
    {
        return $this->todos->findForUser($userId, $id)
            ?? throw ApiExceptions::notFound();
    }
}