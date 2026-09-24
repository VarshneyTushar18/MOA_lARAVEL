<?php

namespace App\Http\Controllers;

use App\Exports\ContactsExport;
use App\Http\Controllers\Concerns\FiltersConsoleDateRange;
use App\Http\Controllers\Concerns\HandlesBulkSelection;
use App\Models\Contact;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    use FiltersConsoleDateRange, HandlesBulkSelection;
    public function store(Request $request)
    {
        $request->validate([
            'fname' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\p{M}\s.\'\-]+$/u'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'fname.regex' => 'Name may only contain letters (including Hindi and other scripts), spaces, apostrophes, hyphens, and periods.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        Contact::create([
            'full_name' => $request->fname,
            'email'     => $request->email,
            'message'   => $request->message,
        ]);

        return back()->with('success', 'Your message has been submitted successfully!');
    }

    public function index(Request $request)
    {
        $query = Contact::query()->latest();
        $filters = $this->dateRangeQuery($request, $query, 'created_at');
        $contacts = $query->paginate(25)->withQueryString();

        return view('contacts_console.list', compact('contacts', 'filters'));
    }

    public function show($id)
    {
        $contact = Contact::findOrFail($id);

        return view('contacts_console.show', compact('contact'));
    }

    public function edit($id)
    {
        $contact = Contact::findOrFail($id);

        return view('contacts_console.edit', compact('contact'));
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\p{M}\s.\'\-]+$/u'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'full_name.regex' => 'Name may only contain letters (including Hindi and other scripts), spaces, apostrophes, hyphens, and periods.',
        ]);

        $contact->update($validated);

        return redirect()
            ->route('console.contacts.show', $contact->id)
            ->with('success', 'Contact submission updated successfully.');
    }

    public function destroy($id)
    {
        Contact::findOrFail($id)->delete();

        return redirect()
            ->route('console.contacts.index')
            ->with('success', 'Contact submission deleted successfully.');
    }

    public function exportSelected(Request $request)
    {
        $ids = $this->validatedBulkIds($request);
        $contacts = Contact::whereIn('id', $ids)->latest()->get();

        return Excel::download(
            new ContactsExport($contacts),
            'contacts_selected_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportAll(Request $request)
    {
        $query = Contact::query()->latest();
        $filters = $this->dateRangeQuery($request, $query, 'created_at');
        $contacts = $query->get();
        $suffix = $this->hasDateRange($filters) ? 'filtered' : 'all';

        return Excel::download(
            new ContactsExport($contacts),
            'contacts_'.$suffix.'_'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function bulkDestroy(Request $request)
    {
        if ($this->bulkUsesDateRange($request)) {
            $query = Contact::query();
            $this->dateRangeQuery($request, $query, 'created_at');
            $deleted = $query->delete();

            return redirect()
                ->route('console.contacts.index', $request->only(['from_date', 'to_date']))
                ->with('success', $deleted.' contact submission(s) deleted for the selected date range.');
        }

        $ids = $this->validatedBulkIds($request);
        $deleted = Contact::whereIn('id', $ids)->delete();

        return redirect()
            ->route('console.contacts.index', $request->only(['from_date', 'to_date']))
            ->with('success', $deleted.' contact submission(s) deleted successfully.');
    }
}