<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;

class HomeController extends Controller
{

    public function index()
    {
        $page = Page::where('slug', 'home')
                    ->with('sections')
                    ->first();

        return view('pages.home', compact('page'));
    }
    public function aboutUs()
{
    $page = Page::with('sections.subsections', 'sections.images')->where('slug', 'about-us')->first();
    return view('pages.about', compact('page'));
}
public function contactUs()
{
    $page = Page::with('sections.subsections', 'sections.images')->where('slug', 'contact-us')->first();
    return view('pages.contact', compact('page'));
}
}
