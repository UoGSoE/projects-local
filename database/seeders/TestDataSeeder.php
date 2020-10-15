<?php

namespace Database\Seeders;

use App\Course;
use App\Programme;
use App\Project;
use App\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'is_staff' => true,
            'is_admin' => true,
        ]);

        $ugradCourses = Course::factory()->count(3)->create(['category' => 'undergrad']);
        $ugradCourses->each(function ($course) {
            $course->students()->saveMany(User::factory()->count(20)->student()->create());
        });

        $pgradCourses = Course::factory()->count(3)->create(['category' => 'postgrad']);
        $pgradCourses->each(function ($course) {
            $course->students()->saveMany(User::factory()->count(20)->student()->create());
        });

        $ugradProgrammes = Programme::factory()->count(10)->create(['category' => 'undergrad']);
        $pgradProgrammes = Programme::factory()->count(10)->create(['category' => 'postgrad']);

        $ugradProjects = Project::factory()->count(65)->create(['category' => 'undergrad', 'type' => 'M.Eng']);
        $ugradProjects = Project::factory()->count(25)->create(['category' => 'undergrad', 'type' => 'B.Eng']);
        $ugradProjects = Project::factory()->count(25)->create(['category' => 'undergrad', 'type' => 'SIT/UESTC']);
        $ugradProjects = Project::factory()->count(10)->create(['category' => 'undergrad', 'type' => 'SIT/UESTC', 'staff_id' => $admin->id]);
        $pgradProjects = Project::factory()->count(65)->create(['category' => 'postgrad']);

        $ugradProjects->each(function ($project) use ($ugradCourses, $ugradProgrammes) {
            $project->courses()->sync([$ugradCourses->shuffle()->first()->id]);
            $project->programmes()->sync($ugradProgrammes->shuffle()->take(4)->pluck('id')->toArray());
        });

        $pgradProjects->each(function ($project) use ($pgradCourses, $pgradProgrammes) {
            $project->courses()->sync([$pgradCourses->shuffle()->first()->id]);
            $project->programmes()->sync($pgradProgrammes->shuffle()->take(4)->pluck('id')->toArray());
        });
    }
}
