<?php

namespace App\Http\Livewire;

use App\Models\Programme;
use Livewire\Component;

class ProgrammeMerger extends Component
{
    public $category = '';

    public $mergeFrom = [];
    public $mergeTo = null;
    public $showProjectLists = [];
    public $showStudentLists = [];

    protected $rules = [
        'mergeFrom' => 'required|array|min:1',
        'mergeTo' => 'required|integer|exists:programmes,id',
    ];

    public function render()
    {
        return view('livewire.programme-merger', [
            'programmes' => $this->getProgrammes(),
        ]);
    }

    public function getProgrammes()
    {
        return Programme::with([
            'projects' => fn ($query) => $query->orderBy('title'),
            'students' => fn ($query) => $query->orderBy('surname'),
            ])
            ->orderBy('title')
            ->when($this->category, fn ($query) => $query->where('category', '=', $this->category))
            ->get();
    }

    public function merge()
    {
        $this->validate();

        $mergeFromProgrammes = Programme::findMany($this->mergeFrom);
        $mergeToProgramme = Programme::find($this->mergeTo);
        $mergeFromProgrammes->each->transferProjectsToProgramme($mergeToProgramme);
        $mergeFromProgrammes->each->transferStudentsToProgramme($mergeToProgramme);
        $this->reset();
    }

    public function remove(int $programmeId)
    {
        $programme = Programme::find($programmeId);

        if ($programme->projects()->count() > 0) {
            return;
        }
        if ($programme->students()->count() > 0) {
            return;
        }

        $programme->delete();
    }

    public function toggleProjectListing(int $programmeId)
    {
        if (in_array($programmeId, $this->showProjectLists)) {
            $this->showProjectLists = array_diff($this->showProjectLists, [$programmeId]);
            return;
        }

        $this->showProjectLists[] = $programmeId;
    }

    public function toggleStudentListing(int $programmeId)
    {
        if (in_array($programmeId, $this->showStudentLists)) {
            $this->showStudentLists = array_diff($this->showStudentLists, [$programmeId]);
            return;
        }

        $this->showStudentLists[] = $programmeId;
    }
}
