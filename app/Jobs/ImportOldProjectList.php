<?php

namespace App\Jobs;

use App\User;
use App\Course;
use App\Project;
use App\Programme;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportOldProjectList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $oldProjectData = [];
    public $oldProjects = [];
    public $ldapUsers = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($oldProjectData)
    {
        $this->oldProjectData = $oldProjectData;
        $this->oldProjects = Cache::remember('oldprojects', 3600, function () {
            return Http::get(config('projects.wlm_api_url') . '/getallprojects')->json()['Data'];
        });
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        collect($this->oldProjectData)->each(function ($row) {
            if (! $row) {
                return;
            }
            if (! preg_match('/(ENG|UESTC|COMPSCI)/', $row[6]) === 1) {
                return;
            }
            $title = trim($row[0]);
            $staff = $this->findUser(trim($row[2]));
            if (! $staff) {
                return;
            }
            $description = $this->lookupDescription($title);
            $preReq = $this->lookupPrereqs($title);
            $courses = $this->findCourses(trim($row[6]));
            $programmes = $this->findProgrammes(trim($row[7]));
            $newProject = Project::firstOrCreate(['title' => $title], [
                'title' => $title,
                'max_students' => $row[4],
                'category' => 'undergrad',
                'staff_id' => $staff->id,
                'description' => $description,
                'pre_req' => $preReq,
            ]);
            $newProject->courses()->sync($courses->pluck('id')->toArray());
            $newProject->programmes()->sync($programmes->pluck('id')->toArray());
        });
    }

    protected function findUser($guid)
    {
        $existingUser = User::where('username', '=', $guid)->first();
        if ($existingUser) {
            return $existingUser;
        }

        $ldapUser = $this->findLdapUser($guid);
        if (! $ldapUser) {
            info('Could not find ldap user ' . $guid);
            return;
        }

        return User::create([
            'username' => strtolower($ldapUser->username),
            'email' => strtolower($ldapUser->email),
            'surname' => $ldapUser->surname,
            'forenames' => $ldapUser->forenames,
            'is_staff' => !$this->looksLikeMatric($ldapUser->username),
            'password' => bcrypt(Str::random(64)),
        ]);
    }

    protected function lookupDescription($title)
    {
        foreach ($this->oldProjects as $project) {
            if (trim($title) == trim($project['Title'])) {
                return trim($project['Description']);
            }
        }
        return 'NOT FOUND';
    }

    protected function lookupPrereqs($title)
    {
        foreach ($this->oldProjects as $project) {
            if (trim($title) == trim($project['Title'])) {
                return trim($project['Prereq']);
            }
        }
        return '';
    }

    protected function findCourses($courses)
    {
        $codes = explode('/', $courses);
        return collect($codes)->filter(function ($code) {
            return trim($code);
        })->map(function ($code) {
            $code = trim($code);
            return Course::firstOrCreate(['code' => $code], [
                'code' => $code,
                'title' => $code,
                'category' => 'undergrad',
            ]);
        });
    }

    protected function findProgrammes($programmes)
    {
        $programmeNames = explode('|', $programmes);
        return collect($programmeNames)->filter(function ($name) {
            return trim($name);
        })->map(function ($name) {
            $name = trim($name);
            return Programme::firstOrCreate(['title' => $name], [
                'title' => $name,
                'category' => 'undergrad',
            ]);
        });
    }



    protected function createNewProjectFromOld($oldProject)
    {
        $ldapUser = $this->findLdapUser($oldProject['Staff'][0]['GUID']);
        if (! $ldapUser) {
            info('Could not find ldap user ' . $oldProject['Staff'][0]['GUID']);
            return;
        }

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

    protected function findLdapUser($guid)
    {
        if (in_array($guid, $this->ldapUsers)) {
            return $this->ldapUsers[$guid];
        }

        $user = \Ldap::findUser($guid);
        if (! $user) {
            return false;
        }

        $this->ldapUsers[$user->username] = $user;

        return $user;
    }

    protected function getLocalUser($ldapUser)
    {
        $user = User::where('username', '=', $ldapUser->username)->first();
        if ($user) {
            return $user;
        }

        return User::create([
            'username' => strtolower($ldapUser->username),
            'email' => strtolower($ldapUser->email),
            'surname' => $ldapUser->surname,
            'forenames' => $ldapUser->forenames,
            'is_staff' => !$this->looksLikeMatric($ldapUser->username),
            'password' => bcrypt(Str::random(64)),
        ]);
    }

    protected function createMissingCourses($courses)
    {
        $courses = explode('/', $courses);
        return collect($courses)->map(function ($code) {
            $code = trim($code);
            $course = Course::where('code', '=', $code)->first();
            if (!$course) {
                $course = Course::create([
                    'code' => $code,
                    'title' => 'NAME OF COURSE',
                    'category' => 'undergrad',
                ]);
            }
            return $course;
        });
    }

    protected function looksLikeMatric($username)
    {
        if (preg_match('/^[0-9]/', $username) === 1) {
            return true;
        }

        return false;
    }
}
