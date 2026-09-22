<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Services\FooterContentService;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function __construct(private FooterContentService $footerContent)
    {
    }

    public function edit()
    {
        return view('footer_console.edit', $this->footerContent->forAdminForm());
    }

    public function update(Request $request)
    {
        $this->footerContent->saveFromRequest($request);

        return redirect('/console/footer')->with('message', 'Footer updated successfully.');
    }
}
