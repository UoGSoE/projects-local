<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class StaffExport implements FromCollection
{
    public function __construct($format)
    {
        $this->format = $format;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $staff = User::staff()
            ->with(['staffProjects.students', 'secondSupervisorProjects'])
            ->orderBy('surname')
            ->get()
            ->map(function ($user) {
                return $user->getProjectStats();
            })->map(function ($staffMember, $key) {
                return [
                    'username' => $staffMember["username"],
                    'surname' => $staffMember["surname"],
                    'forenames' => $staffMember["forenames"],
                    'email' => $staffMember["email"],
                    'ugrad_active' => $staffMember["ugrad_active"],
                    'ugrad_allocated' => $staffMember["ugrad_allocated"],
                    'pgrad_active' => $staffMember["pgrad_active"],
                    'pgrad_allocated' => $staffMember["pgrad_allocated"],
                    '2nd_ugrad_active' => $staffMember["2nd_ugrad_active"],
                    '2nd_ugrad_allocated' => $staffMember["2nd_ugrad_allocated"],
                    '2nd_pgrad_active' => $staffMember["2nd_pgrad_active"],
                    '2nd_pgrad_allocated' => $staffMember["2nd_pgrad_allocated"],
                ];
            });

        if ($this->format == 'xlsx') {
            $staff->prepend([
                'GUID',
                'Surname',
                'Forenames',
                'Email',
                'Ugrad Active',
                'Ugrad Allocated',
                'Pgrad Active',
                'Pgrad Allocated',
                '2nd Ugrad Active',
                '2nd Ugrad Allocated',
                '2nd Pgrad Active',
                '2nd Pgrad Allocated',
            ]);
        }
        return $staff;
    }
}
