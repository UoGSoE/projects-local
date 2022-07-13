<?php

namespace App\Observers;

use App\Events\SomethingNoteworthyHappened;
use App\Models\Course;

class CourseObserver
{
    public function created(Course $course)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Created course {$course->code}"));
    }

    public function updated(Course $course)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Updated course {$course->code}"));
    }

    public function deleting(Course $course)
    {
        event(new SomethingNoteworthyHappened(auth()->user(), "Deleted course {$course->code}"));
    }
}
