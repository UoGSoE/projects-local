<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(): View
    {
        return view('admin.activitylog.index', [
            'logs' => Activity::with('causer')->latest()->simplePaginate(100),
        ]);
    }
}
