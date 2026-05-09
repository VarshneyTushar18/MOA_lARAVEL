<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ConsoleController extends Controller
{
    
    public function logout()
    {
        auth()->logout();
        return redirect('/console/login');
    }

    public function loginForm()
    {
        return view('console.login');
    }

    public function login()
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(auth()->attempt($attributes))
        {
            return redirect('/console/dashboard');
        }
        
        return back()
            ->withInput()
            ->withErrors(['email' => 'Invalid email/password combination']);
    }

    public function dashboard()
    {
        $stats = [
            'total_patients' => Patient::count(),
            'today_patients' => Patient::whereDate('created_at', now()->toDateString())->count(),
            'total_surveys' => SurveyResponse::count(),
            'today_surveys' => SurveyResponse::whereDate('created_at', now()->toDateString())->count(),
        ];

        return view('console.dashboard', compact('stats'));
    }

}
