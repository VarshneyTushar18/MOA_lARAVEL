@extends('layout.console')

@section('content')
<div class="container console-content py-4">
    <div class="console-page-header">
        <h2 class="mb-0">Change Password</h2>
        <div class="console-page-actions">
            <a href="{{ route('console.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
        </div>
    </div>

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card shadow-sm border-0" style="max-width: 520px;">
        <div class="card-body">
            <form method="POST" action="{{ route('console.account.password.update') }}" class="console-form">
                @csrf

                <div class="mb-3">
                    <label for="current_password">Current password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password">New password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation">Confirm new password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
