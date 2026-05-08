<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * Display a page by slug.
     */
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
                    ->with('sections')
                    ->first();

        if (! $page) {
            abort(404);
        }

        // If there's a specific Blade for this page, use it. Otherwise use a generic renderer.
        if (view()->exists('pages.' . $slug)) {
            return view('pages.' . $slug, compact('page'));
        }

        return view('pages.generic', compact('page'));
    }
}
