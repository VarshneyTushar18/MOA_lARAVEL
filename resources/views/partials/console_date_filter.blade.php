@props([
    'action',
    'dateLabel' => 'Submission date',
])

<form method="GET" action="{{ $action }}" class="row g-2 align-items-end mb-3 console-date-filter">
    <div class="col-12 col-md-auto">
        <label class="form-label small mb-1 text-muted">{{ $dateLabel }} from</label>
        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
    </div>
    <div class="col-12 col-md-auto">
        <label class="form-label small mb-1 text-muted">{{ $dateLabel }} to</label>
        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
    </div>
    <div class="col-12 col-md-auto d-flex gap-2 flex-wrap">
        <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
        <a href="{{ $action }}" class="btn btn-outline-secondary btn-sm">Clear</a>
    </div>
    @if(request('from_date') || request('to_date'))
        <div class="col-12">
            <span class="badge text-bg-info">Date filter active</span>
        </div>
    @endif
</form>
