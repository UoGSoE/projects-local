<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Project;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_see_a_list_of_all_projects()
    {
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
    public function an_admin_can_view_a_project()
    {

    }

    public function an_admin_can_delete_a_project()
    {
    }

    /** @test */
    public function an_admin_can_create_a_project_for_a_given_member_of_staff()
    {

    }

    /** @test */
    public function an_admin_can_update_a_project()
    {

    }

    /** @test */
    public function an_admin_can_see_all_student_project_choices()
    {

    }

    /** @test */
    public function an_admin_can_accept_any_student_on_a_given_project()
    {

    }

    /** @test */
    public function an_admin_can_bulk_accept_students_onto_projects()
    {

    }

    /** @test */
    public function an_admin_can_clear_all_students_from_a_given_course()
    {

    }

    /** @test */
    public function an_admin_can_clear_all_students_postgrad_or_undergrad_students()
    {

    }

    /** @test */
    public function an_admin_can_clear_all_students()
    {

    }

    /** @test */
    public function an_admin_can_download_a_csv_of_all_project_data()
    {

    }

    /** @test */
    public function an_admin_can_impersonate_a_student()
    {

    }

    /** @test */
    public function an_admin_can_impersonate_a_member_of_staff()
    {

    }
}
