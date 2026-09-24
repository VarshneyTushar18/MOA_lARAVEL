<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait HandlesBulkSelection
{
    protected function validatedBulkIds(Request $request): array
    {
        return $request->validate([
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer'],
        ])['selected_ids'];
    }

    protected function bulkUsesDateRange(Request $request): bool
    {
        return ! $request->filled('selected_ids')
            && ($request->filled('from_date') || $request->filled('to_date'));
    }
}
