<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Course;
use App\Programme;
use App\Project;

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
        $pgradCourses = factory(Course::class, 3)->create(['category' => 'postgrad']);
        $ugradProgrammes = factory(Programme::class, 3)->create(['category' => 'undergrad']);
        $pgradProgrammes = factory(Programme::class, 3)->create(['category' => 'postgrad']);
        $ugradProjects = factory(Project::class, 15)->create(['category' => 'undergrad']);
        $pgradProjects = factory(Project::class, 15)->create(['category' => 'postgrad']);
        $ugradProjects->each(function ($project) {
            $project->courses()->sync([$ugradCourses->shuffle()->first()->id]);
            $project->programmes()->sync([$ugradProgrammes->shuffle()->slice(4)->pluck(id)->all()]);
        });
        $pgradProjects->each(function ($project) {
            $project->courses()->sync([$pgradCourses->shuffle()->first()->id]);
            $project->programmes()->sync([$pgradProgrammes->shuffle()->slice(4)->pluck(id)->all()]);
        });

        $students = factory(User::class, 20)->states('student')->create();
    }
}
