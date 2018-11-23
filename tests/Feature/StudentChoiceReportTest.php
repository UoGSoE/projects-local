<?php

namespace Tests\Feature;

use App\Course;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentChoiceReportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admins_can_see_a_report_of_all_students_of_a_given_category_and_their_project_choices()
    {
        $admin = create(User::class, ['is_admin' => true]);
        $ugradCourse = create(Course::class, ['category' => 'undergrad']);
        $student1 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $student2 = create(User::class, ['is_staff' => false, 'course_id' => $ugradCourse->id]);
        $pgradCourse = create(Course::class, ['category' => 'postgrad']);
        $postGradStudent = create(User::class, ['is_staff' => false, 'course_id' => $pgradCourse->id]);

        $response = $this->actingAs($admin)->get(route('admin.report.choices', ['category' => 'undergrad']));

        $response->assertOk();
        $response->assertSee('All Undergrad Student Choices');
        $this->assertTrue($response->data('students')->contains($student1));
        $this->assertTrue($response->data('students')->contains($student2));
        $this->assertFalse($response->data('students')->contains($postGradStudent));
    }
}
