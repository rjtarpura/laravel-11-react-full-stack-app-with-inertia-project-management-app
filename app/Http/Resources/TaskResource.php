<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'description'       => $this->description,
            'due_date'          => $this->due_date->format('Y-m-d'),
            'status'            => $this->status,
            'image_path'        => ($this->image_path) ? (Str::startsWith($this->image_path, 'http') ? $this->image_path : Storage::url($this->image_path)) : '',
            'priority'          => $this->priority,
            'project_id'        => $this->project_id,
            'project'           => new ProjectResource($this->project),
            'created_at'        => $this->created_at->format('Y-m-d'),
            'updated_at'        => $this->updated_at->format('Y-m-d'),
            'createdBy'         => new UserResource($this->createdBy),
            'updatedBy'         => new UserResource($this->updatedBy),
            'assigned_user_id'  => $this->assigned_user_id,
            'assignedUser'      => ($this->assignedUser) ? new UserResource($this->assignedUser) : null,
        ];
    }
}
