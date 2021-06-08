<?php

namespace App\Jobs;

use App\Models\Course;
use App\Models\Programme;
use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class ImportDmoranSheetRow implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $row;
    public $rowNumber;
    public $errorSetName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $row, int $rowNumber)
    {
        $this->row = $row;
        $this->rowNumber = $rowNumber;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->errorSetName = optional($this->batch())->id . '-errors';

        $title = $this->row[0];
        $guid = $this->row[1];
        $courses = explode('|', $this->row[4]);
        $maxStudents = trim($this->row[5]);
        $active = $this->row[6];
        $confidential = $this->row[7];
        $placement = $this->row[8];
        $description = $this->row[9];
        $prereq = $this->row[10];
        $programmes = explode('|', $this->row[11]);

        if (! is_numeric($maxStudents)) {
            $maxStudents = 1;
        }
        if (empty($maxStudents)) {
            $maxStudents = 1;
        }

        $staff = User::where('username', '=', $guid)->first();
        if (! $staff) {
            Redis::sadd($this->errorSetName, 'Invalid GUID ' . $guid . ' on row ' . $this->rowNumber);
            return;
        }

        $project = Project::where('title', '=', $title)->first();
        if (! $project) {
            $project = new Project;
        }
        $project->title = $title;
        $project->description = $description;
        $project->pre_req = $prereq;
        $project->is_active = $active == 'Y' ? 1 : 0;
        $project->max_students = $maxStudents;
        $project->is_placement = $placement == 'Y' ? 1 : 0;
        $project->is_confidential = $confidential == 'Y' ? 1 : 0;
        $project->staff_id = $staff->id;
        $project->category = 'undergrad';
        $project->type = 'M.Eng';
        $project->save();

        $courseIds = collect($courses)->map(function ($courseCode) {
            $course = Course::where('code', '=', trim($courseCode))->first();
            if (! $course) {
                Redis::sadd($this->errorSetName, 'Invalid course code ' . $courseCode . ' on row ' . $this->rowNumber);
                return;
            }
            return $course->id;
        })->filter();

        $project->courses()->sync($courseIds);

        $programmeIds = collect($programmes)->map(function ($programmeTitle) {
            $programme = Programme::where('title', '=', trim($programmeTitle))->first();
            if (! $programme) {
                Redis::sadd($this->errorSetName, 'Invalid programme title ' . $programmeTitle . ' on row ' . $this->rowNumber);
                return;
            }
            return $programme->id;
        })->filter();

        $project->programmes()->sync($programmeIds);

        $project->students()->sync([]);
    }
}
