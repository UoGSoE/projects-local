<?php

namespace App\Http\Controllers\Admin;

use App\Events\SomethingNoteworthyHappened;
use App\Exports\StudentListExporter;
use App\Http\Controllers\Controller;
use App\User;

class StudentExportController extends Controller
{
    public function export(string $category)
    {
        $filename = (new StudentListExporter($this->getStudents($category)))->create();

        event(new SomethingNoteworthyHappened(auth()->user(), 'Exported the list of students'));

        return response()->download($filename, "uog_{$category}_project_students.csv", ['Content-Type' => 'text/csv'])->deleteFileAfterSend(true);
    }

    protected function getStudents($category)
    {
        return User::ofType($category)->with('projects')->orderBy('surname')->get();
    }
}
