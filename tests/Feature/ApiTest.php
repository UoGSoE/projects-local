<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /** @test */
    public function the_api_routes_require_a_valid_api_key_to_access_them()
    {
        $response = $this->getJson('/api/students/postgrad');

        $response->assertUnauthorized();

        $response = $this->getJson('/api/students/postgrad', [
            'Authorization' => '',
        ]);

        $response->assertUnauthorized();

        config(['projects.api_key' => 'valid-key']);

        $response = $this->getJson('/api/students/postgrad', [
            'Authorization' => '',
        ]);

        $response->assertUnauthorized();

        $response = $this->getJson('/api/students/postgrad', [
            'Authorization' => 'Bearer invalid-key',
        ]);

        $response->assertUnauthorized();

        $response = $this->getJson('/api/students/postgrad', [
            'Authorization' => 'Bearer valid-key',
        ]);

        $response->assertOk();
    }

    /** @test */
    public function we_can_request_a_list_of_accepted_postgrad_students()
    {
        config(['projects.api_key' => 'valid-key']);
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();
        $student3 = User::factory()->student()->create();
        $student4 = User::factory()->student()->create();
        $project1 = Project::factory()->create(['category' => 'postgrad']);
        $project2 = Project::factory()->create(['category' => 'postgrad']);
        $undergradProject = Project::factory()->create(['category' => 'undergrad']);
        $inactiveProject = Project::factory()->create(['category' => 'postgrad', 'is_active' => false]);

        $project1->addAndAccept($student1);
        $project1->addAndAccept($student2);
        $project2->addAndAccept($student3);
        $student4->projects()->sync([$project2->id => ['is_accepted' => false, 'choice' => 1]]);

        $response = $this->getJson('/api/students/postgrad', [
            'Authorization' => 'Bearer valid-key',
        ]);

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment([
            'username' => $student1->username,
            'email' => $student1->email,
            'surname' => $student1->surname,
            'forenames' => $student1->forenames,
            'supervisor' => [
                'username' => $project1->owner->username,
                'email' => $project1->owner->email,
                'surname' => $project1->owner->surname,
                'forenames' => $project1->owner->forenames,
            ],
        ]);
        $response->assertJsonFragment([
            'username' => $student2->username,
            'email' => $student2->email,
            'surname' => $student2->surname,
            'forenames' => $student2->forenames,
            'supervisor' => [
                'username' => $project1->owner->username,
                'email' => $project1->owner->email,
                'surname' => $project1->owner->surname,
                'forenames' => $project1->owner->forenames,
            ],
        ]);
        $response->assertJsonFragment([
            'username' => $student3->username,
            'email' => $student3->email,
            'surname' => $student3->surname,
            'forenames' => $student3->forenames,
            'supervisor' => [
                'username' => $project2->owner->username,
                'email' => $project2->owner->email,
                'surname' => $project2->owner->surname,
                'forenames' => $project2->owner->forenames,
            ],
        ]);
    }

    /** @test */
    public function we_can_request_a_list_of_accepted_postgrad_students_who_are_flagged_as_tier4()
    {
        config(['projects.api_key' => 'valid-key']);
        $student1 = User::factory()->student()->create(['is_tier4' => true]);
        $student2 = User::factory()->student()->create(['is_tier4' => false]);
        $student3 = User::factory()->student()->create(['is_tier4' => true]);
        $student4 = User::factory()->student()->create();
        $project1 = Project::factory()->create(['category' => 'postgrad']);
        $project2 = Project::factory()->create(['category' => 'postgrad']);
        $undergradProject = Project::factory()->create(['category' => 'undergrad']);
        $inactiveProject = Project::factory()->create(['category' => 'postgrad', 'is_active' => false]);

        $project1->addAndAccept($student1);
        $project1->addAndAccept($student2);
        $project2->addAndAccept($student3);
        $student4->projects()->sync([$project2->id => ['is_accepted' => false, 'choice' => 1]]);

        $response = $this->getJson('/api/students/postgrad?onlytier4=true', [
            'Authorization' => 'Bearer valid-key',
        ]);

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment([
            'username' => $student1->username,
            'email' => $student1->email,
            'surname' => $student1->surname,
            'forenames' => $student1->forenames,
            'supervisor' => [
                'username' => $project1->owner->username,
                'email' => $project1->owner->email,
                'surname' => $project1->owner->surname,
                'forenames' => $project1->owner->forenames,
            ],
        ]);
        $response->assertJsonMissing([
            'username' => $student2->username,
            'email' => $student2->email,
        ]);
        $response->assertJsonFragment([
            'username' => $student3->username,
            'email' => $student3->email,
            'surname' => $student3->surname,
            'forenames' => $student3->forenames,
            'supervisor' => [
                'username' => $project2->owner->username,
                'email' => $project2->owner->email,
                'surname' => $project2->owner->surname,
                'forenames' => $project2->owner->forenames,
            ],
        ]);
    }
}
