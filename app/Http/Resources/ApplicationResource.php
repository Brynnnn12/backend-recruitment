<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'vacancy_id' => $this->vacancy_id,
            'user' => [
                'name' => $this->user->name,
            ],
            'vacancy' => [
                'title' => $this->vacancy->title,
                'location' => $this->vacancy->location,
            ],
            'cv_file' => $this->cv_file,
            'status' => $this->status->value,
            'applied_at' => $this->applied_at,

        ];
    }
}
