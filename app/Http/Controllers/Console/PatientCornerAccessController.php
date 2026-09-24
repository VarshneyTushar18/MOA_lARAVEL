<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Services\PatientCornerAuthService;
use Illuminate\Http\Request;

class PatientCornerAccessController extends Controller
{
    public function __construct(private PatientCornerAuthService $authService)
    {
    }

    public function edit()
    {
        $this->authService->ensureDefaults();

        return view('console.patient_corner_access.edit', [
            'username' => old('username', $this->authService->username()),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:4|max:255',
        ]);

        $this->authService->updateCredentials($validated['username'], $validated['password']);

        return redirect()
            ->route('console.patient_corner_access.edit')
            ->with('message', 'Patient Corner login credentials updated successfully.');
    }
}
