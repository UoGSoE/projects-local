<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Project;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_see_a_list_of_all_projects()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $project1 = create(Project::class);
        $project2 = create(Project::class);

        $response = $this->actingAs($admin)->get(route('admin.project.index'));

        $response->assertSuccessful();
        $response->assertSee($project1->title);
        $response->assertSee($project1->owner->full_name);
        $response->assertSee($project2->title);
        $response->assertSee($project2->owner->full_name);
    }

    /** @test */
    public function an_admin_can_see_all_student_project_choices()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class);
        $project2 = create(Project::class);
        $project3 = create(Project::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student2 = create(User::class, ['is_staff' => false]);
        $student1->projects()->sync([
            $project1->id => ['choice' => 1],
            $project2->id => ['choice' => 2],
        ]);
        $student2->projects()->sync([
            $project1->id => ['choice' => 3],
            $project2->id => ['choice' => 1],
        ]);
        //dd($student1->projects);
        $response = $this->actingAs($admin)->get(route('admin.student.choices'));

        $response->assertSuccessful();
        $response->assertSee($student1->full_name);
        $response->assertSee($student2->full_name);
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);
        $response->assertDontSee($project3->title);
    }
}
