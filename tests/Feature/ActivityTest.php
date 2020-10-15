<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ResearchArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Ohffs\Ldap\LdapUser;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_see_the_activity_log()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->get(route('admin.activitylog'));

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_see_the_activity_log()
    {
        $this->withoutExceptionHandling();
        $user = create(User::class);
        $admin = create(User::class, ['is_admin' => true]);

        // generate some activity
        login($user);
        login($admin);

        $response = $this->actingAs($admin)->get(route('admin.activitylog'));

        $response->assertSuccessful();
        $response->assertSee('Activity Log');
        $this->assertEquals(2, $response->data('logs')->count());
    }

    /** @test */
    public function an_event_is_recorded_when_a_user_is_manually_created()
    {
        if (env('CI')) {
            $this->assertTrue(true);

            return;
        }
        \Ldap::shouldReceive('findUser')->once()->andReturn(new LdapUser([
            0 => [
                'uid' => ['valid123x'],
                'mail' => ['valid@example.org'],
                'sn' => ['Valid'],
                'givenname' => ['Miss'],
                'telephonenumber' => ['12345'],
            ],
        ]));
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('api.user.store'), [
            'guid' => 'valid123x',
        ]);

        $response->assertSuccessful();
        $user = User::where('username', '=', 'valid123x')->first();
        $log = Activity::first();
        $this->assertTrue($log->causer->is($admin));
        $this->assertEquals('Created user Valid, Miss', $log->description);
    }

    /** @test */
    public function an_event_is_recorded_when_a_user_logs_in()
    {
        $user = create(User::class);

        login($user);

        $log = Activity::first();
        $this->assertTrue($log->causer->is($user));
        $this->assertEquals('Logged in', $log->description);
    }

    /** @test */
    public function an_event_is_recorded_when_a_user_is_deleted()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $staff = create(User::class, ['is_staff' => true]);
        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->delete(route('admin.user.delete', $staff->id));

        $log = Activity::first();
        $this->assertTrue($log->causer->is($admin));
        $this->assertEquals("Deleted staffmember {$staff->full_name}", $log->description);

        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->delete(route('admin.user.delete', $student->id));

        $log = Activity::first();
        $this->assertTrue($log->causer->is($admin));
        $this->assertEquals("Deleted student {$student->matric}", $log->description);
    }

    /** @test */
    public function an_event_is_recorded_when_a_user_creates_updates_or_deletes_a_project()
    {
        $staff = create(User::class, ['is_staff' => true]);
        $programme1 = create(Programme::class);
        $programme2 = create(Programme::class);
        $course = create(Course::class);
        Activity::all()->each->delete();

        $response = $this->actingAs($staff)->post(route('project.store'), [
            'category' => 'undergrad',
            'type' => 'B.Eng',
            'title' => 'My new project',
            'pre_req' => 'Some mad skillz',
            'description' => 'Doing something',
            'max_students' => 2,
            'courses' => [$course->id],
            'programmes' => [$programme1->id, $programme2->id],
            'is_confidential' => true,
            'is_placement' => true,
        ]);

        $log = Activity::first();
        $this->assertTrue($log->causer->is($staff));
        $this->assertEquals('Created project My new project', $log->description);

        Activity::all()->each->delete();
        $project = Project::first();

        $response = $this->actingAs($staff)->post(route('project.update', $project->id), [
            'category' => 'undergrad',
            'type' => 'B.Eng',
            'title' => 'NEW TITLE',
            'pre_req' => 'Some mad skillz',
            'description' => 'Doing something',
            'max_students' => 2,
            'courses' => [$course->id],
            'programmes' => [$programme1->id, $programme2->id],
            'is_confidential' => true,
            'is_placement' => true,
        ]);

        $log = Activity::first();
        $this->assertTrue($log->causer->is($staff));
        $this->assertEquals('Updated project NEW TITLE', $log->description);

        Activity::all()->each->delete();
        $originalTitle = $project->fresh()->title;

        $response = $this->actingAs($staff)->delete(route('project.delete', $project->id));

        $log = Activity::first();
        $this->assertTrue($log->causer->is($staff));
        $this->assertEquals("Deleted project {$originalTitle}", $log->description);
    }

    /** @test */
    public function an_event_is_recorded_when_a_student_applies_for_projects()
    {
        $student = create(User::class, ['is_staff' => false]);
        config(['projects.required_choices' => 2]);
        login();
        $projects = create(Project::class, [], 2);
        $area = create(ResearchArea::class);
        Activity::all()->each->delete();

        $response = $this->actingAs($student)->post(route('projects.choose'), [
            'choices' => [
                1 => $projects[0]->id,
                2 => $projects[1]->id,
            ],
            'research_area' => $area->title,
        ]);

        $response->assertSessionHasNoErrors();
        $log = Activity::first();
        $this->assertTrue($log->causer->is($student));
        $this->assertEquals("Applied for projects {$projects[0]->title}, {$projects[1]->title}", $log->description);
    }

    /** @test */
    public function an_event_is_recorded_when_a_student_is_accepted_onto_a_project()
    {
        Mail::fake();
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['staff_id' => $staff->id, 'category' => 'undergrad']);
        $course = create(Course::class, ['allow_staff_accept' => true, 'category' => 'undergrad']);
        $project->courses()->sync([$course->id]);
        $student->projects()->sync([$project->id => ['choice' => 1]]);
        Activity::all()->each->delete();

        $response = $this->actingAs($staff)->post(route('project.accept_students', $project->id), [
            'students' => [$student->id],
        ]);

        $response->assertSessionHasNoErrors();
        $log = Activity::first();
        $this->assertTrue($log->causer->is($staff));
        $this->assertEquals("Accepted students {$student->matric} onto project {$project->title}", $log->description);
    }

    /** @test */
    public function an_event_is_recorded_when_a_student_is_manually_added_to_a_project()
    {
        Mail::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $project = create(Project::class, ['category' => 'undergrad']);
        $student->projects()->sync([$project->id => ['choice' => 2]]);
        $this->assertFalse($project->students()->first()->isAccepted());
        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->post(route('admin.project.add_student', $project->id), [
            'student_id' => $student->id,
        ]);

        $response->assertSessionHasNoErrors();
        $log = Activity::first();
        $this->assertTrue($log->causer->is($admin));
        $this->assertEquals("Manually accepted student {$student->matric} onto project {$project->title}", $log->description);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_imports_2nd_supervisors()
    {
        // see 'ImportSecondSupervisorsTest@admins_can_import_a_spreadsheet_to_update_project_second_supervisors'
        $this->assertTrue(true);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_bulk_edits_options()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $ugProject1 = create(Project::class, ['category' => 'undergrad', 'is_active' => true]);
        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->post(route('admin.project.bulk-options.update', ['category' => 'undergrad']), [
            'active' => [
                [
                    'id' => $ugProject1->id,
                    'is_active' => 0,
                ],
            ],
            'delete' => [
                $ugProject1->id,
            ],
        ]);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Bulk updated project options', $logs[0]->description);
        $this->assertTrue($logs[1]->causer->is($admin));
        $this->assertEquals("Set project {$ugProject1->title} as inactive", $logs[1]->description);
        $this->assertTrue($logs[2]->causer->is($admin));
        $this->assertEquals("Deleted project {$ugProject1->title}", $logs[2]->description);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_exports_the_project_list()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class, ['category' => 'postgrad']);
        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->get(route('export.projects', ['category' => 'postgrad', 'format' => 'csv']));

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Exported the list of projects', $logs[0]->description);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_creates_edits_deletes_a_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        login($admin);
        Activity::all()->each->delete();

        $course = create(Course::class);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Created course {$course->code}", $logs[0]->description);

        Activity::all()->each->delete();
        $course->update(['code' => 'NEW9999']);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Updated course {$course->code}", $logs[0]->description);

        Activity::all()->each->delete();
        $course->delete();

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Deleted course {$course->code}", $logs[0]->description);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_creates_edits_deletes_a_programme()
    {
        $admin = create(User::class, ['is_admin' => true]);
        login($admin);
        Activity::all()->each->delete();

        $programme = create(Programme::class);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Created programme {$programme->title}", $logs[0]->description);

        Activity::all()->each->delete();
        $programme->update(['title' => 'Lasers on Sharks']);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Updated programme Lasers on Sharks', $logs[0]->description);

        Activity::all()->each->delete();
        $programme->delete();

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Deleted programme {$programme->title}", $logs[0]->description);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_imports_students_onto_a_course()
    {
        // see 'EnrollmentTest@an_admin_can_import_a_spreadsheet_of_students_who_are_on_a_course'
        $this->assertTrue(true);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_removes_all_students_from_a_course()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $course = create(Course::class);
        $students = create(User::class, ['is_staff' => false], 3);
        $course->students()->saveMany($students);
        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->delete(route('course.remove_students', $course->id));

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Removed all students from {$course->code}", $logs[0]->description);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_removes_all_students_of_a_given_type()
    {
        // see 'MaintenanceTest@an_admin_can_clear_all_postgrad_or_undergrad_students'
        $this->assertTrue(true);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_bulk_accepts_students()
    {
        $this->withoutExceptionHandling();
        Mail::fake();
        $admin = create(User::class, ['is_admin' => true]);
        $project1 = create(Project::class);
        $student1 = create(User::class, ['is_staff' => false]);
        $student1->projects()->sync([$project1->id => ['choice' => 1]]);
        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->post(route('project.bulk_accept'), [
            'students' => [
                $student1->id => $project1->id,
            ],
        ]);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Bulk accepted 1 students', $logs[0]->description);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_impersonates_a_user()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);
        login($admin);
        Activity::all()->each->delete();

        $response = $this->post(route('impersonate.start', $user->id));

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Started impersonating {$user->full_name}", $logs[0]->description);

        Activity::all()->each->delete();

        $response = $this->delete(route('impersonate.stop'));

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Stopped impersonating {$user->full_name}", $logs[0]->description);
    }

    /** @test */
    public function an_event_is_recorded_when_an_admin_gdpr_exports_a_user()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);
        $staff = create(User::class, ['is_staff' => true]);
        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->get(route('gdpr.export.user', $staff->id));

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals("Exported GDPR data for {$staff->username}", $logs[0]->description);
    }

    /** @test */
    public function an_event_is_recorded_a_research_area_is_added_updated_or_deleted()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);
        Activity::all()->each->delete();

        $response = $this->actingAs($admin)->post(route('researcharea.store'), [
            'title' => 'Fred',
        ]);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Created new research area Fred', $logs[0]->description);

        Activity::all()->each->delete();
        $area = ResearchArea::first();

        $response = $this->actingAs($admin)->post(route('researcharea.update', $area->id), [
            'title' => 'Updated Title',
        ]);

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Updated research area Updated Title', $logs[0]->description);

        Activity::all()->each->delete();
        $area = ResearchArea::first();

        $response = $this->actingAs($admin)->delete(route('researcharea.destroy', $area->id));

        $logs = Activity::all();
        $this->assertTrue($logs[0]->causer->is($admin));
        $this->assertEquals('Deleted research area Updated Title', $logs[0]->description);
    }
}
