<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\BeforeSheet;

class AllocationsImport implements ToModel, WithValidation, WithHeadingRow, SkipsOnFailure, WithEvents
{
    use Importable;
    use SkipsFailures;
    use RegistersEventListeners;

    public static function beforeSheet(BeforeSheet $event)
    {
        //Converting the GUID cell to lowercase
        $highestRow = $event->sheet->getHighestRow();
        for ($row = 1; $row <= $highestRow; $row++) {
            $event->sheet->setCellValueByColumnAndRow(1, $row, strtolower($event->sheet->getCellByColumnAndRow(1, $row)->getValue()));
        }
    }

    public function model(array $row)
    {
        $project = Project::find($row['project_id']);
        $project->unsetEventDispatcher();   // disable the project events being logged so we don't spam the activity log
        $student = User::students()->where('username', '=', $row['guid'])->first();
        $project->addAndAccept($student);

        return $project;
    }

    public function rules(): array
    {
        return [
            'guid' => 'exists:users,username',
            'project_id' => 'exists:projects,id',
        ];
    }
}
