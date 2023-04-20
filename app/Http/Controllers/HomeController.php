<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\ResearchArea;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): View
    {
        if (Auth::user()->isStudent()) {
            return view('student.home', [
                'projects' => Auth::user()->applicableProjects(),
                'programmes' => Auth::user()->applicableProgrammes(),
                'researchAreas' => ResearchArea::orderBy('title')->get(),
            ]);
        }

        return view('staff.home');
    }
}
