<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'quadrant' => $this->quadrant,
            'status' => $this->status,
            'due_date' => $this->due_date->toDateString(),
            'carry_count' => $this->carry_count,
            'postpone_count' => $this->postpone_count,
            'completed_at' => $this->completed_at?->utc()->toIso8601ZuluString(),
            'created_at' => $this->created_at->utc()->toIso8601ZuluString(),
            'updated_at' => $this->updated_at->utc()->toIso8601ZuluString(),
        ];
    }
}