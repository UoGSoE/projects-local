<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('admin.activitylog.index', [
            'logs' => Activity::with('causer')->latest()->simplePaginate(100),
        ]);
    }
}
