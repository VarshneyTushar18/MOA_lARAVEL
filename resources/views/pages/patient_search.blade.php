@extends('layout.frontend')

@section('content')

<section class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1>Patient Search</h1>
                <ul class="breadcrumbs">
                    <li><a href="{{ url('/') }}">Home</a></li>
                    <li><img src="{{ asset('assets/images/double-arrow.svg') }}" alt=""></li>
                    <li>Patient Search</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="patient-search-section py-5">
    <div class="container">

        @if($errors->has('q'))
            <div class="alert alert-danger">{{ $errors->first('q') }}</div>
        @endif

        @if(request('q'))
            <h4 class="mb-4">Search Result for: <strong>{{ request('q') }}</strong></h4>
        @endif

        @if($patients->count())

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>UHID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Diagnosis</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                        <tr>
                            <td>{{ $patient->date }}</td>
                            <td>{{ $patient->uhid_no }}</td>
                            <td>{{ $patient->name }}</td>
                            <td>{{ $patient->age }}</td>
                            <td>{{ $patient->sex }}</td>
                            <td>{{ $patient->diagnosis }}</td>
                            <td>{{ $patient->contact_details }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $patients->links() }}
            </div>

        @else
            <div class="alert alert-warning text-center">
                No Patient Found
            </div>
        @endif

    </div>
</section>

@endsection