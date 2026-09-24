<?php

namespace App\Http\Controllers;

use App\Exports\CurePatientsExport;
use App\Http\Controllers\Concerns\FiltersConsoleDateRange;
use App\Http\Controllers\Concerns\HandlesBulkSelection;
use App\Models\CurePatient;
use App\Services\CompressedUploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CureController extends Controller
{
    use FiltersConsoleDateRange, HandlesBulkSelection;

    public function index(Request $request)
    {
        $query = CurePatient::query()->orderByDesc('id');
        $filters = $this->dateRangeQuery($request, $query, 'created_at');
        $records = $query->paginate(25)->withQueryString();

        return view('cure_console.list', compact('records', 'filters'));
    }

    public function show($id)
    {
        $record = CurePatient::findOrFail($id);

        return view('cure_console.show', compact('record'));
    }

    public function consoleFile($id)
    {
        $record = CurePatient::findOrFail($id);

        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($record->file_path);
    }

    public function consoleDownload($id)
    {
        $record = CurePatient::findOrFail($id);

        if (! $record->file_path || ! Storage::disk('local')->exists($record->file_path)) {
            return back()->with('message', 'File not found on disk.');
        }

        return Storage::disk('local')->download($record->file_path);
    }

    public function edit($id)
    {
        $record = CurePatient::findOrFail($id);

        return view('cure_console.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        $record = CurePatient::findOrFail($id);

        $validated = $request->validate([
            'ltbi_no' => ['required', 'string', 'regex:/^[0-9]{1,12}$/'],
            'cc_no' => ['nullable', 'string', 'regex:/^[0-9]{1,12}$/'],
            'tr_no' => ['nullable', 'string', 'regex:/^[0-9]{1,12}$/'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg'],
        ]);

        if (empty($validated['cc_no']) && empty($validated['tr_no'])) {
            return back()->withInput()->withErrors([
                'cc_no' => 'Enter either CC No or TR No.',
            ]);
        }

        $accessCode = $this->buildAccessCode(
            $validated['ltbi_no'],
            $validated['cc_no'] ?? null,
            $validated['tr_no'] ?? null
        );

        if (CurePatient::where('access_code', $accessCode)->where('id', '!=', $record->id)->exists()) {
            return back()->withInput()->withErrors([
                'ltbi_no' => 'Another record already uses access code '.$accessCode.'.',
            ]);
        }

        if ($request->hasFile('file')) {
            if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
                Storage::disk('local')->delete($record->file_path);
            }

            $file = $request->file('file');
            $filename = $accessCode.'.'.$file->getClientOriginalExtension();
            $validated['file_path'] = CompressedUploadStorage::storeImageAs($file, 'cure', $filename);
        }

        $record->update([
            'ltbi_no' => $validated['ltbi_no'],
            'cc_no' => $validated['cc_no'] ?? null,
            'tr_no' => $validated['tr_no'] ?? null,
            'access_code' => $accessCode,
            'file_path' => $validated['file_path'] ?? $record->file_path,
        ]);

        return redirect()
            ->route('console.cure.show', $record->id)
            ->with('success', 'Cure upload updated successfully.');
    }

    public function destroy($id)
    {
        $this->deleteRecord(CurePatient::findOrFail($id));

        return redirect()
            ->route('console.cure.list')
            ->with('success', 'Cure upload deleted successfully.');
    }

    public function exportSelected(Request $request)
    {
        $ids = $this->validatedBulkIds($request);
        $records = CurePatient::whereIn('id', $ids)->orderByDesc('id')->get();

        return Excel::download(
            new CurePatientsExport($records),
            'cure_uploads_selected_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportAll(Request $request)
    {
        $query = CurePatient::query()->orderByDesc('id');
        $filters = $this->dateRangeQuery($request, $query, 'created_at');
        $records = $query->get();
        $suffix = $this->hasDateRange($filters) ? 'filtered' : 'all';

        return Excel::download(
            new CurePatientsExport($records),
            'cure_uploads_'.$suffix.'_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function bulkDestroy(Request $request)
    {
        if ($this->bulkUsesDateRange($request)) {
            $query = CurePatient::query();
            $this->dateRangeQuery($request, $query, 'created_at');
            $records = $query->get();

            foreach ($records as $record) {
                $this->deleteRecord($record);
            }

            return redirect()
                ->route('console.cure.list', $request->only(['from_date', 'to_date']))
                ->with('success', $records->count().' cure upload(s) deleted for the selected date range.');
        }

        $ids = $this->validatedBulkIds($request);
        $records = CurePatient::whereIn('id', $ids)->get();

        foreach ($records as $record) {
            $this->deleteRecord($record);
        }

        return redirect()
            ->route('console.cure.list', $request->only(['from_date', 'to_date']))
            ->with('success', $records->count().' cure upload(s) deleted successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:cc,tr'],
            'ltbi_no' => ['required', 'string', 'regex:/^[0-9]{1,12}$/'],
            'cc_no' => ['nullable', 'required_if:type,cc', 'string', 'regex:/^[0-9]{1,12}$/'],
            'tr_no' => ['nullable', 'required_if:type,tr', 'string', 'regex:/^[0-9]{1,12}$/'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg'],
        ], [
            'ltbi_no.regex' => 'LTBI number must be numeric only.',
            'cc_no.regex' => 'CC number must be numeric only.',
            'tr_no.regex' => 'TR number must be numeric only.',
            'cc_no.required_if' => 'Enter CC number when CC Number type is selected.',
            'tr_no.required_if' => 'Enter TR number when TR Number type is selected.',
        ]);

        $access_code = $this->buildAccessCode(
            $validated['ltbi_no'],
            $validated['type'] === 'cc' ? $validated['cc_no'] : null,
            $validated['type'] === 'tr' ? $validated['tr_no'] : null
        );

        if (CurePatient::where('access_code', $access_code)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'ltbi_no' => 'This patient document is already uploaded (Access Code: '.$access_code.'). You cannot upload it again.',
                ]);
        }

        // Store File
        $file = $request->file('file');
        $filename = $access_code.'.'.$file->getClientOriginalExtension();
        $path = CompressedUploadStorage::storeImageAs($file, 'cure', $filename);

        // Save DB
        CurePatient::create([
            'ltbi_no' => $validated['ltbi_no'],
            'cc_no' => $validated['cc_no'],
            'tr_no' => $validated['tr_no'],
            'access_code' => $access_code,
            'file_path' => $path,
        ]);

        return back()->with('cure_success', 'File Uploaded Successfully. Access Code: '.$access_code);
    }

    public function download(Request $request)
    {
        $request->validate([
            'access_code' => ['required', 'string', 'regex:/^[0-9]{7}$/'],
        ], [
            'access_code.regex' => 'Access code must be exactly 7 digits.',
        ]);

        $record = CurePatient::where('access_code', $request->input('access_code'))->first();

        if (! $record) {
            return back()->withErrors([
                'access_code' => 'Invalid access code.',
            ]);
        }

        return Storage::download($record->file_path);
    }

    private function buildAccessCode(string $ltbiNo, ?string $ccNo, ?string $trNo): string
    {
        $ltbiLast4 = substr(str_pad($ltbiNo, 5, '0', STR_PAD_LEFT), -4);
        $reference = $ccNo ?: $trNo;
        $last3 = substr(str_pad((string) $reference, 5, '0', STR_PAD_LEFT), -3);

        return $ltbiLast4.$last3;
    }

    private function deleteRecord(CurePatient $record): void
    {
        if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
            Storage::disk('local')->delete($record->file_path);
        }

        $record->delete();
    }
}
