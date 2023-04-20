<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectChoice extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->title,
            'choice' => $this->pivot->choice,
            'accepted' => $this->pivot->is_accepted,
        ];
    }
}
