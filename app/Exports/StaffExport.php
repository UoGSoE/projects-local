<?php

namespace App\Exports;

use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class StaffExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return User::staff()
            ->with(['staffProjects.students', 'secondSupervisorProjects'])
            ->orderBy('surname')
            ->get()
            ->map(function ($user) {
                return $user->getProjectStats();
            })->map(function ($staffMember, $key) {
                return [
                    'username' => $staffMember['username'],
                    'surname' => $staffMember['surname'],
                    'forenames' => $staffMember['forenames'],
                    'email' => $staffMember['email'],
                    'ugrad_beng_active' => $staffMember['ugrad_beng_active'],
                    'ugrad_beng_allocated' => $staffMember['ugrad_beng_allocated'],
                    'ugrad_meng_active' => $staffMember['ugrad_meng_active'],
                    'ugrad_meng_allocated' => $staffMember['ugrad_meng_allocated'],
                    'ugrad_etc_active' => $staffMember['ugrad_etc_active'],
                    'ugrad_etc_allocated' => $staffMember['ugrad_etc_allocated'],
                    'pgrad_active' => $staffMember['pgrad_active'],
                    'pgrad_allocated' => $staffMember['pgrad_allocated'],
                    'second_ugrad_beng_active' => $staffMember['second_ugrad_beng_active'],
                    'second_ugrad_beng_allocated' => $staffMember['second_ugrad_beng_allocated'],
                    'second_ugrad_meng_active' => $staffMember['second_ugrad_meng_active'],
                    'second_ugrad_meng_allocated' => $staffMember['second_ugrad_meng_allocated'],
                    'second_ugrad_etc_active' => $staffMember['second_ugrad_etc_active'],
                    'second_ugrad_etc_allocated' => $staffMember['second_ugrad_etc_allocated'],
                    'second_pgrad_active' => $staffMember['second_pgrad_active'],
                    'second_pgrad_allocated' => $staffMember['second_pgrad_allocated'],
                ];
            })
            ->prepend([
                'GUID',
                'Surname',
                'Forenames',
                'Email',
                'Ugrad B.Eng Active',
                'Ugrad B.Eng Allocated',
                'Ugrad M.Eng Active',
                'Ugrad M.Eng Allocated',
                'Ugrad SIT/UESTC Active',
                'Ugrad SIT/UESTC Allocated',
                'Pgrad Active',
                'Pgrad Allocated',
                '2nd Ugrad B.Eng Active',
                '2nd Ugrad B.Eng Allocated',
                '2nd Ugrad M.Eng Active',
                '2nd Ugrad M.Eng Allocated',
                '2nd Ugrad SIT/UESTC Active',
                '2nd Ugrad SIT/UESTC Allocated',
                '2nd Pgrad Active',
                '2nd Pgrad Allocated',
            ]);
    }
}
