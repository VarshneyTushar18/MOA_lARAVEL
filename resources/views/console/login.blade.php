@extends ('layout.console')

@section ('content')


<div class="container d-flex justify-content-center align-items-center">
    
    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        
        <img src="/assets/images/Main-logo.png" alt="Logo" class="mb-4" style="width: 160px; margin: auto; display: block;">

        <form method="post" action="/console/login" novalidate>
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" 
                    required
                >

                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="form-control @error('password') is-invalid @enderror"
                    required
                >

                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Button -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    Log In
                </button>
            </div>

        </form>

    </div>

</div>

@endsection
        