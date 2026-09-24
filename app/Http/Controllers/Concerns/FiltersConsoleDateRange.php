<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait FiltersConsoleDateRange
{
    protected function validatedDateRange(Request $request): array
    {
        return $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);
    }

    protected function applyDateRange($query, array $filters, string $column = 'created_at'): void
    {
        if (! empty($filters['from_date'])) {
            $query->whereDate($column, '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate($column, '<=', $filters['to_date']);
        }
    }

    protected function dateRangeQuery(Request $request, $query, string $column = 'created_at')
    {
        $filters = $this->validatedDateRange($request);
        $this->applyDateRange($query, $filters, $column);

        return $filters;
    }

    protected function hasDateRange(array $filters): bool
    {
        return ! empty($filters['from_date']) || ! empty($filters['to_date']);
    }

    protected function ensureDateRangeOrIds(Request $request): void
    {
        if ($request->filled('selected_ids')) {
            return;
        }

        $filters = $request->only(['from_date', 'to_date']);

        if (empty($filters['from_date']) && empty($filters['to_date'])) {
            throw ValidationException::withMessages([
                'from_date' => 'Select rows or set a from/to date range first.',
            ]);
        }
    }
}
