<?php

namespace App\Http\Controllers;

use App\Exports\IdCardsExport;
use App\Http\Controllers\Concerns\FiltersConsoleDateRange;
use App\Http\Controllers\Concerns\HandlesBulkSelection;
use App\Models\IdCard;
use App\Services\CompressedUploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class IdCardController extends Controller
{
    use FiltersConsoleDateRange, HandlesBulkSelection;

    public function index(Request $request)
    {
        $query = IdCard::query()->orderByDesc('id');
        $filters = $this->dateRangeQuery($request, $query, 'created_at');
        $records = $query->paginate(25)->withQueryString();

        return view('idcard_console.list', compact('records', 'filters'));
    }

    public function show($id)
    {
        $record = IdCard::findOrFail($id);

        return view('idcard_console.show', compact('record'));
    }

    public function consoleFile($id)
    {
        $record = IdCard::findOrFail($id);

        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($record->file_path);
    }

    public function consoleDownload($id)
    {
        $record = IdCard::findOrFail($id);

        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            return back()->with('message', 'File not found on disk.');
        }

        return Storage::disk('local')->download($record->file_path);
    }

    public function edit($id)
    {
        $record = IdCard::findOrFail($id);

        return view('idcard_console.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        $record = IdCard::findOrFail($id);

        $request->merge([
            'id_number' => strtoupper(trim((string) $request->input('id_number'))),
        ]);

        $validated = $request->validate([
            'id_number' => ['required', 'string', 'regex:/^ID\d{4}$/'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg'],
        ], [
            'id_number.regex' => 'ID must look like ID0001 (ID + 4 digits).',
        ]);

        if (IdCard::where('id_number', $validated['id_number'])->where('id', '!=', $record->id)->exists()) {
            return back()->withInput()->withErrors([
                'id_number' => 'This ID number is already used by another record.',
            ]);
        }

        if ($request->hasFile('file')) {
            if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
                Storage::disk('local')->delete($record->file_path);
            }

            $file = $request->file('file');
            $filename = $validated['id_number'].'.'.$file->getClientOriginalExtension();
            $validated['file_path'] = CompressedUploadStorage::storeImageAs($file, 'id_cards', $filename);
        }

        $record->update([
            'id_number' => $validated['id_number'],
            'file_path' => $validated['file_path'] ?? $record->file_path,
        ]);

        return redirect()
            ->route('console.idcard.show', $record->id)
            ->with('success', 'ID card upload updated successfully.');
    }

    public function destroy($id)
    {
        $this->deleteRecord(IdCard::findOrFail($id));

        return redirect()
            ->route('console.idcard.list')
            ->with('success', 'ID card upload deleted successfully.');
    }

    public function exportSelected(Request $request)
    {
        $ids = $this->validatedBulkIds($request);
        $records = IdCard::whereIn('id', $ids)->orderByDesc('id')->get();

        return Excel::download(
            new IdCardsExport($records),
            'id_cards_selected_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportAll(Request $request)
    {
        $query = IdCard::query()->orderByDesc('id');
        $filters = $this->dateRangeQuery($request, $query, 'created_at');
        $records = $query->get();
        $suffix = $this->hasDateRange($filters) ? 'filtered' : 'all';

        return Excel::download(
            new IdCardsExport($records),
            'id_cards_'.$suffix.'_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function bulkDestroy(Request $request)
    {
        if ($this->bulkUsesDateRange($request)) {
            $query = IdCard::query();
            $this->dateRangeQuery($request, $query, 'created_at');
            $records = $query->get();

            foreach ($records as $record) {
                $this->deleteRecord($record);
            }

            return redirect()
                ->route('console.idcard.list', $request->only(['from_date', 'to_date']))
                ->with('success', $records->count().' ID card upload(s) deleted for the selected date range.');
        }

        $ids = $this->validatedBulkIds($request);
        $records = IdCard::whereIn('id', $ids)->get();

        foreach ($records as $record) {
            $this->deleteRecord($record);
        }

        return redirect()
            ->route('console.idcard.list', $request->only(['from_date', 'to_date']))
            ->with('success', $records->count().' ID card upload(s) deleted successfully.');
    }

    public function store(Request $request)
    {
        $request->merge([
            'id_number' => strtoupper(trim((string) $request->input('id_number'))),
        ]);

        $validated = $request->validate([
            'id_number' => ['required', 'string', 'regex:/^ID\d{4}$/'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg'],
        ], [
            'id_number.regex' => 'ID must look like ID0001 (ID + 4 digits).',
        ]);

        $idNumber = $validated['id_number'];

        if (IdCard::where('id_number', $idNumber)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'id_number' => 'This ID number is already uploaded. You cannot upload it again.',
                ]);
        }

        // Store file
        $file = $request->file('file');
        $filename = $idNumber.'.'.$file->getClientOriginalExtension();
        $path = CompressedUploadStorage::storeImageAs($file, 'id_cards', $filename);

        IdCard::create([
            'id_number' => $idNumber,
            'file_path' => $path,
        ]);

        return back()->with('id_success', 'ID Card uploaded successfully.');
    }

    public function download(Request $request)
    {
        $request->merge([
            'id_number' => strtoupper(trim((string) $request->input('id_number'))),
        ]);

        $request->validate([
            'id_number' => ['required', 'string', 'regex:/^ID\d{4}$/'],
        ], [
            'id_number.regex' => 'ID must look like ID0001 (ID + 4 digits).',
        ]);

        $record = IdCard::where('id_number', $request->id_number)->first();

        if (! $record) {
            return back()->withErrors([
                'id_number' => 'ID card file not found for this number.',
            ]);
        }

        return Storage::download($record->file_path);
    }

    private function deleteRecord(IdCard $record): void
    {
        if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
            Storage::disk('local')->delete($record->file_path);
        }

        $record->delete();
    }
}
