@extends('layout.console')

@section('content')
<section class="w3-padding">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="mb-0">Patient Corner Access</h2>
        <a href="/console/dashboard" class="btn btn-secondary btn-sm">Back to Dashboard</a>
    </div>

    <p class="text-muted small mb-4">Change the username and password used to unlock the public Patient Corner page.</p>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <form method="POST" action="{{ route('console.patient_corner_access.update') }}" class="console-form card">
        @csrf
        <div class="w3-margin-bottom">
            <label for="username">Username</label>
            <input type="text" class="form-control" name="username" id="username" value="{{ $username }}" required>
        </div>
        <div class="w3-margin-bottom">
            <label for="password">New Password</label>
            <input type="password" class="form-control" name="password" id="password" required minlength="4">
            <small class="text-muted">Enter the new password that Patient Corner users will use.</small>
        </div>
        <button type="submit" class="btn btn-success">Save Credentials</button>
    </form>
</section>
@endsection
