@extends('layout.frontend')

@section('content')
<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Patient Corner</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li>Patient Corner Login</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="ntpcsection py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card p-4 shadow-sm">
                    <h4 class="mb-3 text-center">Patient Corner Access</h4>
                    <p class="text-muted text-center mb-4">This section is restricted. Please sign in to continue.</p>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('patient_corner.login.submit') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}" required autocomplete="username">
                        </div>
                        <div class="mb-4">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Sign In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
