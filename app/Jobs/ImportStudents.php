<?php

namespace App\Jobs;

use App\Notifications\ImportStudentsComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportStudents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public $course;

    public $admin;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $course, $admin)
    {
        $this->data = $data;
        $this->course = $course;
        $this->admin = $admin;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->course->enrollStudents($this->data);
        $this->admin->notify(new ImportStudentsComplete($this->course));
    }
}
