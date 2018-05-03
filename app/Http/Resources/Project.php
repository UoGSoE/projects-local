<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Project extends JsonResource
{
    public function toArray($request)
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'active' => $this->is_active,
        ];
    }
}
