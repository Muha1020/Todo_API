<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    /** Attributes that mass assignment may set. Counters are NOT here on purpose. */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'quadrant',
        'status',
        'due_date',
        'completed_at',
    ];

    /** Defaults for a new todo, matching the brief: pending, both counters at 0. */
    protected $attributes = [
        'description' => null,
        'status' => 'pending',
        'carry_count' => 0,
        'postpone_count' => 0,
        'completed_at' => null,
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'carry_count' => 'integer',
            'postpone_count' => 'integer',
        ];
    }
}
