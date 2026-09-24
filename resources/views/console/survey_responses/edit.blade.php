@extends('layout.console')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Edit Survey Response #{{ $response->id }}</h2>
        <a href="{{ route('console.survey_responses.show', $response) }}" class="btn btn-secondary btn-sm">Back</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-3">
        <form method="POST" action="{{ route('console.survey_responses.update', $response) }}">
            @csrf
            @method('PUT')

            <h4 class="mb-3">Basic Information</h4>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $response->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Contact Details</label>
                    <input type="text" name="contact_details" class="form-control" value="{{ old('contact_details', $response->contact_details) }}" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="2" required>{{ old('address', $response->address) }}</textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select</option>
                        <option value="Male" @selected(old('gender', $response->gender) === 'Male')>Male</option>
                        <option value="Female" @selected(old('gender', $response->gender) === 'Female')>Female</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control" value="{{ old('age', $response->age) }}" min="0" max="120">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Registration Number</label>
                    <input type="text" name="registration_number" class="form-control" value="{{ old('registration_number', $response->registration_number) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Survey Date</label>
                    <input type="date" name="survey_date" class="form-control" value="{{ old('survey_date', optional($response->survey_date)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $response->email) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $response->phone) }}">
                </div>
                <div class="col-md-12 mb-3">
                    <label>Message</label>
                    <textarea name="message" class="form-control" rows="2">{{ old('message', $response->message) }}</textarea>
                </div>
            </div>

            <h4 class="mb-3">Survey Answers</h4>
            <p class="text-muted small">Edit individual answer fields below.</p>

            @php
                $answers = is_array($response->answers) ? $response->answers : [];
            @endphp

            <div class="row">
                @foreach($answerFields as $key => $label)
                    @php
                        $current = data_get($answers, $key);
                        $value = old('answers.'.$key, is_array($current) ? implode(', ', $current) : $current);
                    @endphp
                    <div class="col-md-6 mb-3">
                        <label>{{ $label }}</label>
                        <textarea name="answers[{{ $key }}]" class="form-control" rows="2">{{ $value }}</textarea>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-success">Save Changes</button>
        </form>
    </div>
</div>
@endsection
