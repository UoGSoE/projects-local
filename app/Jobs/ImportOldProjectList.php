<?php

namespace App\Jobs;

use App\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ImportOldProjectList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $oldProjectData = [];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oldProjectData)
    {
        $this->oldProjectData = $oldProjectData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $oldProjects = Cache::remember('oldprojects', 3600, function () {
            return Http::get(config('projects.wlm_api_url') . '/getallprojects')->json();
        });
        collect($this->oldProjectData)->each(function ($row) use ($oldProjects) {
            if (! $row) {
                return;
            }
            $title = $row[0];
            foreach ($oldProjects['Data'] as $project) {
                if ($project['Title'] == $title) {
                    $this->createNewProjectFromOld($project);
                    return;
                }
            }
            info('Could not import old project ' . $title);
        });
    }

    protected function createNewProjectFromOld($oldProject)
    {
        $ldapUser = $this->findLdapUser($oldProject['Staff'][0]['GUID']);
        $localUser = $this->getLocalUser($ldapUser);
        $courses = $this->createMissingCourses($oldProject['Courses']);
        $programmes = $this->createMissingProgrammes($oldProject['Programme']);
        Project::create([
            'title' => $oldProject['Title'],
            'description' => $oldProject['Description'],
            'pre_req' => $oldProject['Prereq'],
            'max_students' => $oldProject['NumStudents'],
            'is_confidential' => $oldProject['ConfidentialFlag'],
            'is_active' => true,
            'is_placement' => $oldProject['Placement'],
        ]);
    }
}
