@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Contact #{{ $contact->id }}</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('console.contacts.edit', $contact->id) }}" class="btn btn-primary btn-sm">Edit</a>
            <form action="{{ route('console.contacts.destroy', $contact->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this contact submission?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
            <a href="{{ route('console.contacts.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3"><strong>Name:</strong> {{ $contact->full_name ?: '—' }}</div>
                <div class="col-md-6 mb-3"><strong>Email:</strong> {{ $contact->email ?: '—' }}</div>
                <div class="col-md-6 mb-3"><strong>Submitted:</strong> {{ optional($contact->created_at)->format('d-m-Y h:i A') ?: '—' }}</div>
            </div>

            <hr>

            <h5 class="mb-3">Message</h5>
            <div class="text-muted">{{ $contact->message ?: '—' }}</div>
        </div>
    </div>
</div>
@endsection
