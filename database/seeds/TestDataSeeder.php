<?php

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
        $admin = factory(User::class)->create([
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'is_staff' => true,
            'is_admin' => true,
        ]);

        $ugradCourses = factory(Course::class, 3)->create(['category' => 'undergrad']);
        $ugradCourses->each(function ($course) {
            $course->students()->saveMany(factory(User::class, 20)->states('student')->create());
        });

        $pgradCourses = factory(Course::class, 3)->create(['category' => 'postgrad']);
        $pgradCourses->each(function ($course) {
            $course->students()->saveMany(factory(User::class, 20)->states('student')->create());
        });

        $ugradProgrammes = factory(Programme::class, 10)->create(['category' => 'undergrad']);
        $pgradProgrammes = factory(Programme::class, 10)->create(['category' => 'postgrad']);

        $ugradProjects = factory(Project::class, 65)->create(['category' => 'undergrad', 'type' => 'M.Eng']);
        $ugradProjects = factory(Project::class, 25)->create(['category' => 'undergrad', 'type' => 'B.Eng']);
        $ugradProjects = factory(Project::class, 25)->create(['category' => 'undergrad', 'type' => 'SIT/UESTC']);
        $pgradProjects = factory(Project::class, 65)->create(['category' => 'postgrad']);

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
