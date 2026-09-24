<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePatientCornerAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->get('patient_corner_authenticated') === true) {
            return $next($request);
        }

        return redirect()
            ->route('patient_corner.login')
            ->with('error', 'Please sign in to access Patient Corner.');
    }
}
