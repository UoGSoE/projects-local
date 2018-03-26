<?php

namespace Tests\Feature\Student;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_student_can_edit_their_profile()
    {
        $student = create(User::class, ['is_staff' => false]);

        $response = $this->actingAs($student)->get(route('profile.edit'));

        $response->assertSuccessful();
    }

    /** @test */
    public function a_student_can_update_their_profile()
    {
        $student = create(User::class, ['is_staff' => false]);

        $response = $this->actingAs($student)->post(route('profile.update'), [
            'profile' => 'MY EXCITING PROFILE INFO',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');
        $this->assertEquals('MY EXCITING PROFILE INFO', $student->fresh()->profile);
    }

    /** @test */
    public function profile_text_can_be_parsed_as_markdown()
    {
        $student = create(User::class, ['is_staff' => false, 'profile' => "# Hello\nI love the following:\n- deep\n- fried\n- pizza\n"]);

        $this->assertEquals(
            "<h1>Hello</h1>\n<p>I love the following:</p>\n<ul>\n<li>deep</li>\n<li>fried</li>\n<li>pizza</li>\n</ul>",
            $student->getFormattedProfile()
        );
    }
}
