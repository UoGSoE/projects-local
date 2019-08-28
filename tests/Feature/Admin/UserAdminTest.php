<?php

namespace Tests\Feature\Admin;

use App\User;
use App\Course;
use App\Project;
use Tests\TestCase;
use Ohffs\Ldap\LdapUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function regular_users_cant_see_the_user_admin_pages()
    {
        $user = create(User::class);

        $response = $this->actingAs($user)->get(route('admin.users', 'staff'));

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /** @test */
    public function admins_can_see_a_list_of_all_staff()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'surname' => 'Aardvark']);
        $staff1 = create(User::class, ['is_staff' => true, 'surname' => 'Bee']);
        $staff2 = create(User::class, ['is_staff' => true, 'surname' => 'Sea']);
        $ugradCourse = create(Course::class, ['category' => 'undergrad']);
        $student1 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $student2 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $student3 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $student4 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $student5 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $student6 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);

        $staff1UgradActiveProjects = create(Project::class, ['staff_id' => $staff1->id, 'category' => 'undergrad', 'is_active' => true], 2);
        $staff1UgradAllocatedProjects = create(Project::class, ['staff_id' => $staff1->id, 'category' => 'undergrad', 'is_active' => true, 'max_students' => 1], 1);
        $staff1UgradAllocatedProjects->each->addAndAccept($student1);

        $staff1PgradActiveProjects = create(Project::class, ['staff_id' => $staff1->id, 'category' => 'postgrad', 'is_active' => true], 1);
        $staff1PgradAllocatedProjects = create(Project::class, ['staff_id' => $staff1->id, 'category' => 'postgrad', 'is_active' => true, 'max_students' => 1], 1);
        $staff1PgradAllocatedProjects->each->addAndAccept($student2);

        $staff2UgradActiveProjects = create(Project::class, ['staff_id' => $staff2->id, 'category' => 'undergrad', 'is_active' => true], 3);
        $staff2PgradActiveProjects = create(Project::class, ['staff_id' => $staff2->id, 'category' => 'postgrad', 'is_active' => true], 3);

        $staff1SecondUgradActiveProjects = create(Project::class, ['staff_id' => $admin->id, 'second_supervisor_id' => $staff1->id, 'category' => 'undergrad', 'is_active' => true], 1);
        $staff1SecondUgradAllocatedProjects = create(Project::class, ['staff_id' => $admin->id, 'second_supervisor_id' => $staff1->id, 'category' => 'undergrad', 'is_active' => true, 'max_students' => 1], 1);
        $staff1SecondUgradAllocatedProjects->each->addAndAccept($student3);

        $staff1SecondPgradActiveProjects = create(Project::class, ['staff_id' => $admin->id, 'second_supervisor_id' => $staff1->id, 'category' => 'postgrad', 'is_active' => true], 2);
        $staff1SecondPgradAllocatedProjects = create(Project::class, ['staff_id' => $admin->id, 'second_supervisor_id' => $staff1->id, 'category' => 'postgrad', 'is_active' => true, 'max_students' => 1], 1);
        $staff1SecondPgradAllocatedProjects->each->addAndAccept($student4);

        $staff2SecondUgradActiveProjects = create(Project::class, ['staff_id' => $admin->id, 'second_supervisor_id' => $staff2->id, 'category' => 'undergrad', 'is_active' => true], 3);
        $staff2SecondPgradActiveProjects = create(Project::class, ['staff_id' => $admin->id, 'second_supervisor_id' => $staff2->id, 'category' => 'postgrad', 'is_active' => true], 2);

        $response = $this->actingAs($admin)->get(route('admin.users', 'staff'));

        $response->assertSuccessful();
        $response->assertSee($staff1->surname);
        $response->assertSee($staff2->surname);
        $response->assertDontSee($student1->surname);
        // we have three staff users - admin + two staff
        $this->assertEquals(3, $response->data('users')->count());
        // check primary project totals for $staff1
        tap($response->data('users')[1], function ($staff) {
            $this->assertEquals(3, $staff['ugrad_active']);
            $this->assertEquals(1, $staff['ugrad_allocated']);
            $this->assertEquals(2, $staff['pgrad_active']);
            $this->assertEquals(1, $staff['pgrad_allocated']);
        });
        // check 2nd supervisor project totals for $staff1
        tap($response->data('users')[1], function ($staff) {
            $this->assertEquals(2, $staff['2nd_ugrad_active']);
            $this->assertEquals(1, $staff['2nd_ugrad_allocated']);
            $this->assertEquals(3, $staff['2nd_pgrad_active']);
            $this->assertEquals(1, $staff['2nd_pgrad_allocated']);
        });
        // check primary project totals for $staff2
        tap($response->data('users')[2], function ($staff) {
            $this->assertEquals(3, $staff['ugrad_active']);
            $this->assertEquals(0, $staff['ugrad_allocated']);
            $this->assertEquals(3, $staff['pgrad_active']);
            $this->assertEquals(0, $staff['pgrad_allocated']);
        });
        // check 2nd supervisor project totals for $staff2
        tap($response->data('users')[2], function ($staff) {
            $this->assertEquals(3, $staff['2nd_ugrad_active']);
            $this->assertEquals(0, $staff['2nd_ugrad_allocated']);
            $this->assertEquals(2, $staff['2nd_pgrad_active']);
            $this->assertEquals(0, $staff['2nd_pgrad_allocated']);
        });
    }

    /** @test */
    public function admins_can_see_a_list_of_all_undergrads()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $ugradCourse = create(Course::class, ['category' => 'undergrad']);
        $pgradCourse = create(Course::class, ['category' => 'postgrad']);
        $ugradProject = create(Project::class, ['category' => 'undergrad']);
        $undergrad1 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $undergrad2 = create(User::class, ['is_staff' => false]);
        $postgrad = create(User::class, ['is_staff' => false, 'course_id' => $pgradCourse->id]);
        $ugradProject->students()->sync([$undergrad2->id => ['choice' => 1]]);

        $response = $this->actingAs($admin)->get(route('admin.users', 'undergrad'));

        $response->assertSuccessful();
        $response->assertSee($undergrad1->surname);
        $response->assertSee($undergrad2->surname);
        $response->assertDontSee($postgrad->surname);
    }

    /** @test */
    public function admins_can_see_a_list_of_all_postgrads()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true]);
        $ugradCourse = create(Course::class, ['category' => 'undergrad']);
        $pgradCourse = create(Course::class, ['category' => 'postgrad']);
        $ugradProject = create(Project::class, ['category' => 'undergrad']);
        $undergrad1 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $undergrad2 = create(User::class, ['is_staff' => false]);
        $postgrad = create(User::class, ['is_staff' => false, 'course_id' => $pgradCourse->id]);
        $ugradProject->students()->sync([$undergrad2->id => ['choice' => 1]]);

        $response = $this->actingAs($admin)->get(route('admin.users', 'postgrad'));

        $response->assertSuccessful();
        $response->assertDontSee($undergrad1->surname);
        $response->assertDontSee($undergrad2->surname);
        $response->assertSee($postgrad->surname);
    }

    /** @test */
    public function admins_can_give_and_take_away_admin_rights_to_other_users()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);
        $this->assertFalse($user->fresh()->isAdmin());

        $response = $this->actingAs($admin)->post(route('admin.users.toggle_admin', $user->id));

        $response->assertSuccessful();
        $response->assertJson(['status' => 'ok']);
        $this->assertTrue($user->fresh()->isAdmin());

        $response = $this->actingAs($admin)->post(route('admin.users.toggle_admin', $user->id));

        $response->assertSuccessful();
        $response->assertJson(['status' => 'ok']);
        $this->assertFalse($user->fresh()->isAdmin());
    }

    /** @test */
    public function admins_cant_take_away_admin_rights_from_themselves()
    {
        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.users.toggle_admin', $admin->id));

        $response->assertStatus(422);
        $this->assertTrue($admin->fresh()->isAdmin());
    }

    /** @test */
    public function admins_can_view_an_individual_staff_user()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        $response = $this->actingAs($admin)->get(route('admin.user.show', $user->id));

        $response->assertSuccessful();
        $response->assertSee($user->full_name);
    }

    /** @test */
    public function admins_can_view_an_individual_student_user()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class, ['is_staff' => false]);

        $response = $this->actingAs($admin)->get(route('admin.user.show', $user->id));

        $response->assertSuccessful();
        $response->assertSee($user->full_name);
    }

    /** @test */
    public function admins_can_edit_a_users_email_address()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $user = create(User::class);

        $response = $this->actingAs($admin)->postJson(route('api.user.update', $user->id), [
            'email' => 'fred@example.com',
        ]);

        $response->assertSuccessful();
        $this->assertEquals('fred@example.com', $user->fresh()->email);
    }

    /** @test */
    public function admins_can_add_a_new_user()
    {
        $this->withoutExceptionHandling();
        \Ldap::shouldReceive('findUser')->once()->andReturn(new LdapUser([
            0 => [
                'uid' => ['valid123x'],
                'mail' => ['valid@example.org'],
                'sn' => ['Valid'],
                'givenname' => ['Miss'],
                'telephonenumber' => ['12345'],
            ]
        ]));

        $admin = create(User::class, ['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('api.user.store'), [
            'guid' => 'valid123x',
        ]);

        $response->assertSuccessful();
        $user = User::where('username', '=', 'valid123x')->first();
        $this->assertEquals('valid@example.org', $user->email);
        $this->assertEquals('Valid', $user->surname);
        $this->assertEquals('Miss', $user->forenames);
    }
}
