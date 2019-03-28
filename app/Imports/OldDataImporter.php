<?php

namespace App\Imports;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OldDataImporter
{
    private $json;

    public function __construct(string $json)
    {
        $this->json = json_decode($json, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('JSON was invalid');
        }
    }

    public function import()
    {
        DB::transaction(function () {
            foreach ($this->json['Data'] as $jsonProject) {
                $staff = $this->extractStaffInfo($jsonProject);
                $project = $this->extractProjectInfo($jsonProject, $staff);
                $programmes = $this->extractProgrammeInfo($jsonProject);
                $project->programmes()->sync($programmes->pluck('id')->toArray());
                $courses = $this->extractCourseInfo($jsonProject);
                $project->courses()->sync($courses->pluck('id')->toArray());
            }
        });
        return true;
    }

    protected function extractStaffInfo($jsonProject)
    {
        $jsonStaff = array_shift($jsonProject['Staff']);
        return User::updateOrCreate(['username' => $jsonStaff['GUID']], [
            'username' => $jsonStaff['GUID'],
            'surname' => $jsonStaff['Surname'],
            'forenames' => $jsonStaff['Forenames'],
            'is_staff' => true,
            'email' => $jsonStaff['GUID'] . '@campus.gla.ac.uk',
            'password' => bcrypt(Str::random(64)),
        ]);
    }

    public function extractProgrammeInfo($jsonProject)
    {
        $programmes = collect(explode('|', $jsonProject['Programme']));
        $newCategory = $jsonProject['ProjectType'] == 'FYP' ? 'undergrad' : 'postgrad';

        return $programmes->map(function ($title) use ($newCategory) {
            return Programme::updateOrCreate(['title' => $title, 'category' => $newCategory], [
                'title' => $title,
                'category' => $newCategory,
            ]);
        });
    }

    protected function extractCourseInfo($jsonProject)
    {
        $codes = collect($jsonProject['Courses']);
        $newCategory = $jsonProject['ProjectType'] == 'FYP' ? 'undergrad' : 'postgrad';

        return $codes->map(function ($code) use ($newCategory) {
            return Course::updateOrCreate(['code' => $code, 'category' => $newCategory], [
                'code' => $code,
                'title' => "CHANGEME",
                'category' => $newCategory,
                'application_deadline' => now(),
            ]);
        });
    }

    protected function extractProjectInfo($jsonProject, $staff)
    {
        return Project::create([
            'title' => $jsonProject['Title'],
            'category' => $jsonProject['ProjectType'] == 'FYP' ? 'undergrad' : 'postgrad',
            'is_confidential' => $jsonProject['ConfidentialFlag'] == 'Yes' ? true : false,
            'is_placement' => $jsonProject['Placement'] ? true : false,
            'description' => $jsonProject['Description'],
            'pre_req' => $jsonProject['Prereq'],
            'is_active' => true,
            'max_students' => $jsonProject['NumStudents'],
            'staff_id' => $staff->id,
        ]);
    }
}
