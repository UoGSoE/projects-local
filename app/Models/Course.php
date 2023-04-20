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
            $username = trim($username);
            $user = User::where('username', '=', $username)->first();
            if (! $user) {
                $user = new User([
                    'username' => $username,
                    'password' => bcrypt(Str::random(64)),
                ]);
            }

            // in a try/catch as I'm too lazy to do a check to see if these columns exist
            // (different T.O people use different formats)
            try {
                $programmePlanCode = strtoupper($row[7]);
                $programmeTitle = $row[8];
                $programme = Programme::where('plan_code', '=', $programmePlanCode)
                            ->orWhere('title', '=', $programmeTitle)
                            ->first();
                if (! $programme) {
                    $programme = Programme::create([
                        'title' => $programmeTitle,
                        'plan_code' => $programmePlanCode,
                        'category' => $this->category,
                    ]);
                }
            } catch (\Exception $e) {
                $programme = new Programme();
            }

            $user->surname = $row[1];
            $user->forenames = $row[2];
            $user->email = $username.'@student.gla.ac.uk';
            $user->course_id = $this->id;
            $user->programme_id = $programme->id;
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
