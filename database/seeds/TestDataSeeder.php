<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Course;
use App\Programme;

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
        $courses = create(Course::class, [], 3);
        $programmes = create(Programme::class, [], 6);
    }
}
