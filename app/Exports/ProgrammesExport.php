<?php

namespace App\Exports;

use App\Programme;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProgrammesExport implements FromCollection
{
    public function collection()
    {
        $programmes = Programme::with('projects.students')
            ->orderBy('title')
            ->withCount('projects')
            ->get()
            ->each->append('places_count', 'accepted_count');
        return $programmes->map(function ($programme, $key) {
            return [
                'title' => $programme["title"],
                'category' => $programme["category"],
                'projects_count' => $programme["projects_count"],
                'places_count' => $programme["places_count"],
                'accepted_count' => $programme["accepted_count"],
            ];
        })
        ->prepend([
            'Name',
            'Category',
            'Projects',
            'Places',
            'Accepted'
        ]);
    }
}
