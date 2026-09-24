<?php

namespace App\Http\Controllers;

use App\Services\PatientCornerAuthService;
use Illuminate\Http\Request;

class PatientCornerAuthController extends Controller
{
    public function __construct(private PatientCornerAuthService $authService)
    {
    }

    public function showLogin()
    {
        if (session('patient_corner_authenticated') === true) {
            return redirect()->route('patient_corner.index');
        }

        return view('pages.patient_corner_login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        if (! $this->authService->verify($credentials['username'], $credentials['password'])) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Invalid username or password.');
        }

        $request->session()->put('patient_corner_authenticated', true);

        return redirect()->route('patient_corner.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('patient_corner_authenticated');

        return redirect()
            ->route('patient_corner.login')
            ->with('success', 'You have been signed out of Patient Corner.');
    }
}
