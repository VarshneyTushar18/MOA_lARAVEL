@extends('layout.console')

@section('content')
<div class="login-center w-100 d-flex flex-grow-1 justify-content-center align-items-center py-4 px-2 px-sm-3">
    <div class="card shadow w-100" style="max-width: 420px;">
        <div class="card-body">
            <h4 class="mb-3 text-center">Forgot Password</h4>
            <p class="small text-muted text-center">A reset link will be sent to the authorized admin email only.</p>

            @if(session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif

            <form method="POST" action="{{ route('console.password.email') }}" class="console-form">
                @csrf
                <input type="hidden" name="email" value="philtbi533@gmail.com">
                <div class="mb-3">
                    <label for="email_display" class="form-label">Admin Email</label>
                    <input type="email"
                           id="email_display"
                           class="form-control bg-light"
                           value="philtbi533@gmail.com"
                           readonly
                           autocomplete="email">
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                    <a href="{{ route('console.login') }}" class="btn btn-outline-secondary">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
