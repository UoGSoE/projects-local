<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'application_deadline' => 'datetime',
        'allow_staff_accept' => 'boolean',
    ];

    public function students()
    {
        return $this->hasMany(User::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_courses');
    }

    public function scopeUndergrad($query)
    {
        return $query->where('category', '=', 'undergrad');
    }

    public function scopePostgrad($query)
    {
        return $query->where('category', '=', 'postgrad');
    }

    public function removeAllStudents()
    {
        $this->students->each->delete();
    }

    public function enrollStudents($spreadsheetRows)
    {
        return collect($spreadsheetRows)->filter(function ($row) {
            return $this->firstColumnIsMatricLike($row);
        })->map(function ($row) {
            if ($this->isAFullGuid($row[0])) {
                $username = strtolower($row[0]);
            } else {
                $username = strtolower($this->joinMatricAndFirstInitial($row));
            }
            $user = User::where('username', '=', $username)->first();
            if (! $user) {
                $user = new User([
                    'username' => $username,
                    'password' => bcrypt(Str::random(64)),
                ]);
            }
            $user->surname = $row[1];
            $user->forenames = $row[2];
            $user->email = $username.'@student.gla.ac.uk';
            $user->course_id = $this->id;
            $user->save();

            return $user->id;
        });
    }

    protected function firstColumnIsMatricLike(array $row): bool
    {
        return preg_match('/^[0-9]{7}[a-zA-Z]*/', $row[0]) === 1;
    }

    protected function isAFullGuid(string $username): bool
    {
        return preg_match('/^[0-9]{7}[a-zA-Z]$/', $username) === 1;
    }

    protected function joinMatricAndFirstInitial($row): string
    {
        return $row[0].strtolower(substr($row[1], 0, 1));
    }
}
