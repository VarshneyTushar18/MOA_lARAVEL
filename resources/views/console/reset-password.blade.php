@extends('layout.console')

@section('content')
<div class="login-center w-100 d-flex flex-grow-1 justify-content-center align-items-center py-4 px-2 px-sm-3">
    <div class="card shadow w-100" style="max-width: 420px;">
        <div class="card-body">
            <h4 class="mb-3 text-center">Reset Password</h4>

            <form method="POST" action="{{ route('console.password.update') }}" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
