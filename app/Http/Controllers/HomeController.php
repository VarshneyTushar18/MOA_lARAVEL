<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Patient;
use App\Models\SurveyResponse;

class HomeController extends Controller
{

    public function index()
    {
        $page = Page::where('slug', 'home')
                    ->with('sections')
                    ->first();

        $liveStats = [
            'total_patients' => Patient::count(),
            'today_patients' => Patient::whereDate('created_at', now()->toDateString())->count(),
            'total_surveys' => SurveyResponse::count(),
            'today_surveys' => SurveyResponse::whereDate('created_at', now()->toDateString())->count(),
        ];

        return view('pages.home', compact('page', 'liveStats'));
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
