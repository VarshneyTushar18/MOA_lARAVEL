@extends('layout.console')

@section('content')

<div class="container py-4">
    <h2>Survey Submissions</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card p-3 mb-4">
        <h4>Upload Past Data CSV/Excel</h4>

        <form method="POST" action="{{ route('console.survey_responses.import') }}" enctype="multipart/form-data">
            @csrf

            <input type="file" name="file" class="form-control mb-3" required>

            <button type="submit" class="btn btn-primary">
                Import Data
            </button>
        </form>
    </div>

    <div class="card p-3">
        <h4>Survey Responses</h4>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Registration No.</th>
                    <th>Date</th>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($responses as $response)
                    <tr>
                        <td>{{ $response->id }}</td>
                        <td>{{ $response->name }}</td>
                        <td>{{ $response->contact_details }}</td>
                        <td>{{ $response->gender }}</td>
                        <td>{{ $response->age }}</td>
                        <td>{{ $response->registration_number }}</td>
                        <td>{{ optional($response->survey_date)->format('d-m-Y') }}</td>
                        <td>{{ $response->created_at->format('d-m-Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No survey responses found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $responses->links() }}
    </div>
</div>

@endsection
