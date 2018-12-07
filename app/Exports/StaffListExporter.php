<?php

namespace App\Exports;

use Ohffs\SimpleSpout\ExcelSheet;

class StaffListExporter
{
    protected $staff;

    public function __construct($staff)
    {
        $this->staff = $staff;
    }

    public function create()
    {
        return (new ExcelSheet)->generate($this->staffToArray());
    }

    protected function staffToArray()
    {
        return $this->staff->map(function ($staffMember, $key) {
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
        })->prepend([
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
        ])->toArray();
    }
}
