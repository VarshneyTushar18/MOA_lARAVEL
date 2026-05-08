<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fname'   => 'required|string|max:255',
            'email' => ['required', 'email', 'regex:/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/'],
            'message' => 'required|string',
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