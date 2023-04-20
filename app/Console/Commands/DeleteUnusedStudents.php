<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteUnusedStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:deleteunusedstudents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete any students not assigned to a course or project';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        User::students()->each(function ($student) {
            if ($student->course_id) {
                return;
            }

            if ($student->projects()->count() > 0) {
                return;
            }

            \Log::info('Auto-removed student account : '.$student->username);
            $student->delete();
        });
    }
}
