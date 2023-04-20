<?php

namespace Tests\Feature;

use App\Mail\GdprAnonymisedUsers;
use App\Models\Course;
use App\Models\Project;
use App\Models\User;
// use Facades\Ohffs\Ldap\LdapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Ohffs\Ldap\LdapService;
use Tests\TestCase;

class GdprTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_export_all_data_about_a_user_as_json(): void
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_staff' => true, 'is_admin' => true]);
        $staff = create(User::class, ['is_staff' => true]);
        $student = create(User::class, ['is_staff' => false]);
        $course = create(Course::class);
        $course->students()->save($student);
        $project = create(Project::class, ['staff_id' => $staff->id]);
        $project->students()->sync([$student->id => ['is_accepted' => true, 'choice' => 1]]);

        $response = $this->actingAs($admin)->get(route('gdpr.export.user', $student->id));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'username' => $student->username,
                'surname' => $student->surname,
                'forenames' => $student->forenames,
                'email' => $student->email,
                'course' => $student->course->code,
                'projects' => [
                    0 => [
                        'title' => $project->title,
                        'choice' => 1,
                        'accepted' => true,
                    ],
                ],
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('gdpr.export.user', $staff->id));

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'username' => $staff->username,
                'surname' => $staff->surname,
                'forenames' => $staff->forenames,
                'email' => $staff->email,
                'projects' => [
                    0 => [
                        'title' => $project->title,
                        'category' => $project->category,
                        'active' => $project->is_active,
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function an_artisan_command_can_anonymise_staff_accounts_if_they_have_left(): void
    {
        Mail::fake();
        $this->mock(LdapService::class, function ($ldap) {
            $ldap->shouldReceive('findUser')->with('ihaveleft9x')->andReturn(false);
            $ldap->shouldReceive('findUser')->with('stillhere5x')->andReturn(true);
            $ldap->shouldReceive('findUser')->with('leftrecently3x')->andReturn(false);
        });
        // LdapService::shouldReceive('findUser')->once()->with('ihaveleft9x')->andReturn(false);
        // LdapService::shouldReceive('findUser')->once()->with('stillhere5x')->andReturn(true);
        // LdapService::shouldReceive('findUser')->once()->with('leftrecently3x')->andReturn(false);
        config(['projects.gdpr_anonymise_after' => 365]);
        config(['projects.gdpr_contact' => 'test@example.com']);

        $staff1 = create(User::class, ['is_staff' => true, 'username' => 'ihaveleft9x', 'left_at' => now()->subDays(366), 'forenames' => 'FRED']);
        $staff2 = create(User::class, ['is_staff' => true, 'username' => 'stillhere5x', 'left_at' => null, 'forenames' => 'JENNY']);
        $staff3 = create(User::class, ['is_staff' => true, 'username' => 'leftrecently3x', 'left_at' => now()->subDays(5), 'forenames' => 'ANNE']);
        $student = create(User::class, ['is_staff' => false, 'username' => '9999999left', 'forenames' => 'CAROL']);
        $staff1Username = $staff1->username;

        Artisan::call('projects:gdpranonymise');

        $this->assertEquals("ANON{$staff1->id}", $staff1->fresh()->forenames);
        $this->assertEquals('JENNY', $staff2->fresh()->forenames);
        $this->assertEquals('ANNE', $staff3->fresh()->forenames);
        $this->assertEquals('CAROL', $student->fresh()->forenames);

        Mail::assertQueued(GdprAnonymisedUsers::class, function ($mail) use ($staff1, $staff1Username) {
            $this->assertCount(1, $mail->users);
            $this->assertEquals("ANON{$staff1->id}", $mail->users->first()['anonName']);
            $this->assertEquals($staff1Username, $mail->users->first()['originalName']);

            return true;
        });
    }
}
