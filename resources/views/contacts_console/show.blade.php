@extends('layout.console')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Contact #{{ $contact->id }}</h2>
        <a href="/console/contacts/list" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

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
