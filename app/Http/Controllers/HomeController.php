<?php

namespace App\Http\Controllers;

use App\ResearchArea;
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
    public function index()
    {
        if (Auth::user()->isStudent()) {
            return view('student.home', [
                'projects' => Auth::user()->applicableProjects()->toArray(),
                'programmes' => Auth::user()->applicableProgrammes()->toArray(),
                'researchAreas' => ResearchArea::orderBy('title')->get(),
            ]);
        }

        return view('staff.home');
    }
}
