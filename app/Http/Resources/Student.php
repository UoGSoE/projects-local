<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Activitylog\Models\Activity;

class Student extends JsonResource
{
    public function toArray($request)
    {
        return [
            'username' => $this->username,
            'surname' => $this->surname,
            'forenames' => $this->forenames,
            'email' => $this->email,
            'course' => $this->course->code,
            'created' => $this->created_at->format('Y-m-d H:i'),
            'updated' => $this->updated_at->format('Y-m-d H:i'),
            'projects' => ProjectChoice::collection($this->projects),
            'logs' => ActivityLog::collection(Activity::where('causer_id', '=', $this->id)->get()),
        ];
    }

    public function withResponse($request, $response)
    {
        $response->withHeaders([
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="project_student_'.$this->username.'.json"',
        ]);
    }
}
