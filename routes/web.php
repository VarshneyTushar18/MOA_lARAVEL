<?php

use App\Models\Project;
use App\Http\Controllers\ConsoleController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\TypesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageSectionsController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\CureController;
use App\Http\Controllers\ResearchPatientController;
use App\Http\Controllers\IdCardController;
use App\Http\Controllers\SurveyResponseController;
use App\Http\Controllers\Console\SurveyResponseController as ConsoleSurveyResponseController;
/*
|--------------------------------------------------------------------------
| Web Routes    
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [HomeController::class, 'aboutUs']);
Route::get('/contact', [HomeController::class, 'contactUs']);
Route::get('/factsheet', function() {
    $page = \App\Models\Page::where('slug', 'factsheet')
        ->with([
            'sections' => function ($query) {
                $query->with([
                    'images',
                    'media',
                    'highlightItems',
                    'subsections' => function ($subQuery) {
                        $subQuery->with(['images', 'media', 'highlightItems'])->orderBy('sort_order');
                    },
                ])->orderBy('sort_order');
            },
        ])->firstOrFail();
    return view('pages.factsheet', compact('page'));
});

Route::get('/acsm_iec', function() {
    $page = \App\Models\Page::where('slug','acsm_iec')->first();
    return view('pages.acsm_iec', compact('page'));
});

Route::get('/best_practices', function () {
    $page = \App\Models\Page::where('slug','best_practices')->firstOrFail();
    return view('pages.best_practices', compact('page'));
});

Route::get('/patient_corner', function () {
    $page = \App\Models\Page::where('slug','patient_corner')->first();
    return view('pages.patient_corner', compact('page'));
});

Route::get('/performance_report', function () {
    $page = \App\Models\Page::where('slug','performance_report')->first();
    return view('pages.performance_report', compact('page'));
});


Route::get('/patient-search', [PatientController::class, 'search'])->name('patient.search');

Route::get('/console/contacts/list', [ContactController::class, 'index'])->middleware('auth');
Route::post('/contact-submit', [ContactController::class, 'store'])
     ->name('contact.store');

Route::get('/console/logout', [ConsoleController::class, 'logout'])->middleware('auth')->name('console.logout');
Route::redirect('/console', '/console/login');
Route::redirect('/console.login', '/console/login');
Route::get('/console/login', [ConsoleController::class, 'loginForm'])->middleware('guest')->name('console.login');
Route::post('/console/login', [ConsoleController::class, 'login'])->middleware('guest')->name('console.login.submit');
Route::get('/console/dashboard', [ConsoleController::class, 'dashboard'])->middleware('auth')->name('console.dashboard');

// Console: pages and page sections
Route::get('/console/pages/list', [App\Http\Controllers\PagesController::class, 'list'])->middleware('auth');
Route::get('/console/pages/add', [App\Http\Controllers\PagesController::class, 'addForm'])->middleware('auth');
Route::post('/console/pages/add', [App\Http\Controllers\PagesController::class, 'add'])->middleware('auth');
Route::get('/console/pages/edit/{page:id}', [App\Http\Controllers\PagesController::class, 'editForm'])->where('page', '[0-9]+')->middleware('auth');
Route::post('/console/pages/edit/{page:id}', [App\Http\Controllers\PagesController::class, 'edit'])->where('page', '[0-9]+')->middleware('auth');
Route::get('/console/pages/delete/{page:id}', [App\Http\Controllers\PagesController::class, 'delete'])->where('page', '[0-9]+')->middleware('auth');

Route::get('/console/pages/sections/{page:id}/list', [App\Http\Controllers\PageSectionsController::class, 'list'])->where('page', '[0-9]+')->middleware('auth');
Route::get('/console/pages/sections/{page:id}/add', [App\Http\Controllers\PageSectionsController::class, 'addForm'])->where('page', '[0-9]+')->middleware('auth');
Route::post('/console/pages/sections/{page:id}/add', [App\Http\Controllers\PageSectionsController::class, 'add'])->where('page', '[0-9]+')->middleware('auth');
Route::get('/console/pages/sections/{page:id}/edit/{section:id}', [App\Http\Controllers\PageSectionsController::class, 'editForm'])->where('page', '[0-9]+')->where('section', '[0-9]+')->middleware('auth');
Route::post('/console/pages/sections/{page:id}/edit/{section:id}', [App\Http\Controllers\PageSectionsController::class, 'edit'])->where('page', '[0-9]+')->where('section', '[0-9]+')->middleware('auth');
Route::get('/console/pages/sections/{page:id}/delete/{section:id}', [App\Http\Controllers\PageSectionsController::class, 'delete'])->where('page', '[0-9]+')->where('section', '[0-9]+')->middleware('auth');

Route::get('/console/pages/sections/image/delete/{image}', [PageSectionsController::class, 'deleteImage'])->middleware('auth');

// Store patient data
Route::post('/patients/store', [PatientController::class, 'store'])->name('patients.store');

// Admin console list
Route::get('/console/patients/list', [PatientController::class, 'index'])->middleware('auth');

// Download single patient by ID
Route::get('/console/patients/{id}/download', [PatientController::class, 'download'])
    ->middleware('auth')
    ->name('patients.download');

Route::post('/upload-opd', [PatientController::class, 'uploadOpd'])
    ->name('upload.opd');

Route::get('/download-opd', [PatientController::class, 'downloadOpd'])
    ->name('download.opd');
Route::get('/download-opd-by-last4-file', [PatientController::class, 'downloadOpdByLast4AndFileNo'])
    ->name('download.opd.last4_file');


Route::post('/cure/store', [CureController::class, 'store'])
    ->name('cure.store');

Route::get('/cure/download', [CureController::class, 'download'])
    ->name('cure.download');


Route::post('/research/store', [ResearchPatientController::class, 'store'])
    ->name('research.store');

Route::get('/research/download', [ResearchPatientController::class, 'download'])
    ->name('research.download');

Route::post('/idcard/store', [IdCardController::class, 'store'])
    ->name('idcard.store');

Route::get('/idcard/download', [IdCardController::class, 'download'])
    ->name('idcard.download');


//
Route::post('/survey-submit', [SurveyResponseController::class, 'store'])->name('survey.submit');
Route::get('/screening-performa', [SurveyResponseController::class, 'create'])->name('survey.form');

Route::get('/console/survey-responses', [ConsoleSurveyResponseController::class, 'index'])->middleware('auth')->name('console.survey_responses.index');
Route::get('/console/survey-responses/export', [ConsoleSurveyResponseController::class, 'export'])->middleware('auth')->name('console.survey_responses.export');
Route::get('/console/survey-responses/sample-file', [ConsoleSurveyResponseController::class, 'sampleFile'])->middleware('auth')->name('console.survey_responses.sample_file');
Route::post('/console/survey-responses/export-selected', [ConsoleSurveyResponseController::class, 'exportSelected'])->middleware('auth')->name('console.survey_responses.export_selected');
Route::get('/console/survey-responses/{surveyResponse}', [ConsoleSurveyResponseController::class, 'show'])->middleware('auth')->name('console.survey_responses.show');
Route::delete('/console/survey-responses/{surveyResponse}', [ConsoleSurveyResponseController::class, 'destroy'])->middleware('auth')->name('console.survey_responses.destroy');

Route::post('/console/survey-responses/import', [ConsoleSurveyResponseController::class, 'import'])->middleware('auth')->name('console.survey_responses.import');

// Dynamic page route - must be last so it doesn't collide with other routes
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[A-z0-9\-]+');

