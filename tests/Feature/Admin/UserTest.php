<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function admin_can_start_editing_a_users_info()
    {
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $staff = create(User::class, ['is_staff' => true]);

        $response = $this->actingAs($admin)->get(route('admin.user.edit', $staff->id));

        $response->assertSuccessful();
        $response->assertSee("Edit $staff->full_name");
    }

    /** @test */
    public function admin_can_update_a_users_forenames_and_surname()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $staff = create(User::class, [
            'forenames' => 'Jane',
            'surname' => 'Doe',
            'is_staff' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.user.update', $staff->id), [
            'forenames' => 'John',
            'surname' => 'Smith',
            'username' => $staff->username,
            'email' => $staff->email,
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', [
            'forenames' => 'Jane',
            'surname' => 'Doe',
        ]);
        $this->assertDatabaseHas('users', [
            'forenames' => 'John',
            'surname' => 'Smith',
        ]);
    }

    /** @test */
    public function updating_a_students_guid_will_update_their_email()
    {
        $this->withoutExceptionHandling();
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $student = create(User::class, [
            'is_staff' => false,
            'username' => '123456789',
            'email' => '123456789@example.com',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.user.update', $student->id), [
            'forenames' => $student->forenames,
            'surname' => $student->surname,
            'username' => '999999999',
            'email' => $student->email,
        ]);

        $response->assertStatus(302);
        $response->assertSessionMissing('errors');
        $this->assertDatabaseMissing('users', [
            'username' => '123456789',
            'email' => '123456789@example.com',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => '999999999',
            'email' => '999999999@example.com',
        ]);
    }

    /** @test */
    public function cannot_update_a_user_if_guid_already_exists()
    {
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $student1 = create(User::class, [
            'is_staff' => false,
            'username' => '123456789',
            'email' => '123456789@example.com',
        ]);
        $student2 = create(User::class, [
            'is_staff' => false,
            'username' => '999999999',
            'email' => '999999999@example.com',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.user.update', $student1->id), [
            'forenames' => $student1->forenames,
            'surname' => $student1->surname,
            'username' => '999999999',
            'email' => $student1->email,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('username');

        $this->assertDatabaseHas('users', [
            'id' => $student1->id,
            'username' => '123456789',
            'email' => '123456789@example.com',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $student2->id,
            'username' => '999999999',
            'email' => '999999999@example.com',
        ]);
    }

    /** @test */
    public function cannot_update_a_user_if_email_already_exists()
    {
        $admin = create(User::class, ['is_admin' => true, 'is_staff' => true]);
        $student1 = create(User::class, [
            'is_staff' => false,
            'username' => '123456789',
            'email' => '123456789@example.com',
        ]);
        $student2 = create(User::class, [
            'is_staff' => false,
            'username' => '999999999',
            'email' => '999999999@example.com',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.user.update', $student1->id), [
            'forenames' => $student1->forenames,
            'surname' => $student1->surname,
            'username' => $student1->username,
            'email' => $student2->email,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $student1->id,
            'username' => '123456789',
            'email' => '123456789@example.com',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $student2->id,
            'username' => '999999999',
            'email' => '999999999@example.com',
        ]);
    }
}
