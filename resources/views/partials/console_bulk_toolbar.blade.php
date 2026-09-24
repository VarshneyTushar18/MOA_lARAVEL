@props([
    'exportRoute',
    'deleteRoute',
    'exportAllRoute' => null,
    'formId' => 'bulkActionsForm',
])

@php
    $hasDateFilter = request('from_date') || request('to_date');
@endphp

<div class="d-flex justify-content-end gap-2 mb-3 flex-wrap">
    @if($exportAllRoute)
        <a href="{{ $exportAllRoute }}" class="btn btn-outline-success btn-sm">
            {{ $hasDateFilter ? 'Export Filtered' : 'Export All' }}
        </a>
    @endif
    <button type="button" class="btn btn-success btn-sm bulk-export-btn" disabled>
        Export Selected
    </button>
    <button type="button"
            class="btn btn-danger btn-sm bulk-delete-btn"
            data-delete-action="{{ $deleteRoute }}"
            disabled>
        Delete Selected
    </button>
    <button type="button"
            class="btn btn-outline-danger btn-sm bulk-delete-range-btn"
            data-delete-action="{{ $deleteRoute }}"
            @disabled(! $hasDateFilter)>
        Delete Date Range
    </button>
</div>
