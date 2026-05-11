<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
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

    public function index()
{
    $contacts = \App\Models\Contact::latest()->paginate(10);

    return view('contacts_console.list', compact('contacts'));
}
}