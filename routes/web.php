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
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\SurveyResponseController;
use App\Http\Controllers\Console\SurveyResponseController as ConsoleSurveyResponseController;
use App\Http\Controllers\Console\FooterController;
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

Route::get('/favicon.ico', function () {
    $path = public_path('assets/images/favicon.png');

    abort_unless(is_file($path), 404);

    return response()->file($path, ['Content-Type' => 'image/png']);
});

Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [HomeController::class, 'aboutUs']);
Route::get('/contact', [HomeController::class, 'contactUs']);
Route::get('/pm-tb-mukt-bharat-abhiyan', [HomeController::class, 'pmTbMuktBharat'])->name('pm-tb-mukt-bharat');
Route::get('/about-rntcp', [HomeController::class, 'aboutRntcp'])->name('about-rntcp');
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
    $page = \App\Models\Page::where('slug','acsm_iec')
        ->with(['sections.images', 'sections.media', 'sections.subsections.images', 'sections.subsections.media'])
        ->firstOrFail();
    return view('pages.acsm_iec', compact('page'));
});

Route::get('/best_practices', function () {
    $page = \App\Models\Page::where('slug','best_practices')
        ->with(['sections.images', 'sections.media', 'sections.subsections.images', 'sections.subsections.media'])
        ->firstOrFail();
    return view('pages.best_practices', compact('page'));
});

Route::get('/patient_corner', function () {
    $page = \App\Models\Page::where('slug','patient_corner')->first();
    return view('pages.patient_corner', compact('page'));
});

Route::get('/performance_report', function () {
    $page = \App\Models\Page::where('slug','performance_report')
        ->with(['sections.images', 'sections.media'])
        ->firstOrFail();
    return view('pages.performance_report', compact('page'));
});


Route::get('/patient-search', [PatientController::class, 'search'])->name('patient.search');

Route::get('/console/contacts/list', [ContactController::class, 'index'])->middleware('auth');
Route::get('/console/contacts/{id}', [ContactController::class, 'show'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.contacts.show');
Route::post('/contact-submit', [ContactController::class, 'store'])
     ->name('contact.store');

Route::get('/console/logout', [ConsoleController::class, 'logout'])->middleware('auth')->name('console.logout');
Route::redirect('/console', '/console/login');
Route::redirect('/console.login', '/console/login');
Route::get('/console/login', [ConsoleController::class, 'loginForm'])->middleware('guest')->name('console.login');
Route::post('/console/login', [ConsoleController::class, 'login'])->middleware('guest')->name('console.login.submit');
Route::get('/console/dashboard', [ConsoleController::class, 'dashboard'])->middleware('auth')->name('console.dashboard');

Route::get('/console/footer', [FooterController::class, 'edit'])->middleware('auth')->name('console.footer.edit');
Route::post('/console/footer', [FooterController::class, 'update'])->middleware('auth')->name('console.footer.update');

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
Route::get('/console/pages/sections/{page:id}/move/{section:id}/{direction}', [App\Http\Controllers\PageSectionsController::class, 'move'])->where('page', '[0-9]+')->where('section', '[0-9]+')->where('direction', 'up|down')->middleware('auth');

Route::get('/console/pages/sections/image/delete/{image}', [PageSectionsController::class, 'deleteImage'])->middleware('auth');
Route::get('/console/pages/sections/{page:id}/image/delete-main/{section:id}', [PageSectionsController::class, 'deleteMainImage'])->where('page', '[0-9]+')->where('section', '[0-9]+')->middleware('auth');
Route::get('/console/pages/sections/media/delete/{media}', [PageSectionsController::class, 'deleteMedia'])->middleware('auth');

// Store patient data
Route::post('/patients/store', [PatientController::class, 'store'])->name('patients.store');

// Admin console list
Route::get('/console/patients/list', [PatientController::class, 'index'])->middleware('auth');

Route::get('/console/patients/{id}', [PatientController::class, 'show'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.patients.show');

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

Route::get('/console/cure-patients/list', [CureController::class, 'index'])
    ->middleware('auth')
    ->name('console.cure.list');
Route::get('/console/cure-patients/{id}', [CureController::class, 'show'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.cure.show');
Route::get('/console/cure-patients/{id}/file', [CureController::class, 'consoleFile'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.cure.file');
Route::get('/console/cure-patients/{id}/download', [CureController::class, 'consoleDownload'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.cure.download');


Route::post('/research/store', [ResearchPatientController::class, 'store'])
    ->name('research.store');

Route::get('/research/download', [ResearchPatientController::class, 'download'])
    ->name('research.download');

Route::get('/console/research-patients/list', [ResearchPatientController::class, 'index'])
    ->middleware('auth')
    ->name('console.research.list');
Route::get('/console/research-patients/{id}', [ResearchPatientController::class, 'show'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.research.show');
Route::get('/console/research-patients/{id}/file', [ResearchPatientController::class, 'consoleFile'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.research.file');
Route::get('/console/research-patients/{id}/download', [ResearchPatientController::class, 'consoleDownload'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.research.download');

Route::post('/idcard/store', [IdCardController::class, 'store'])
    ->name('idcard.store');

Route::get('/idcard/download', [IdCardController::class, 'download'])
    ->name('idcard.download');

Route::get('/console/id-cards/list', [IdCardController::class, 'index'])
    ->middleware('auth')
    ->name('console.idcard.list');
Route::get('/console/id-cards/{id}', [IdCardController::class, 'show'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.idcard.show');
Route::get('/console/id-cards/{id}/file', [IdCardController::class, 'consoleFile'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.idcard.file');
Route::get('/console/id-cards/{id}/download', [IdCardController::class, 'consoleDownload'])
    ->middleware('auth')
    ->where('id', '[0-9]+')
    ->name('console.idcard.download');


//
Route::post('/survey-submit', [SurveyResponseController::class, 'store'])->name('survey.submit');
Route::get('/screening-performa', [SurveyResponseController::class, 'create'])->name('survey.form');

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/album/{section}', [GalleryController::class, 'show'])
    ->where('section', '[0-9]+')
    ->name('gallery.show');

Route::get('/console/survey-responses', [ConsoleSurveyResponseController::class, 'index'])->middleware('auth')->name('console.survey_responses.index');
Route::get('/console/survey-responses/export', [ConsoleSurveyResponseController::class, 'export'])->middleware('auth')->name('console.survey_responses.export');
Route::get('/console/survey-responses/sample-file', [ConsoleSurveyResponseController::class, 'sampleFile'])->middleware('auth')->name('console.survey_responses.sample_file');
Route::post('/console/survey-responses/export-selected', [ConsoleSurveyResponseController::class, 'exportSelected'])->middleware('auth')->name('console.survey_responses.export_selected');
Route::get('/console/survey-responses/{surveyResponse}', [ConsoleSurveyResponseController::class, 'show'])->middleware('auth')->name('console.survey_responses.show');
Route::delete('/console/survey-responses/{surveyResponse}', [ConsoleSurveyResponseController::class, 'destroy'])->middleware('auth')->name('console.survey_responses.destroy');

Route::post('/console/survey-responses/import', [ConsoleSurveyResponseController::class, 'import'])->middleware('auth')->name('console.survey_responses.import');

// Dynamic page route - must be last so it doesn't collide with other routes
Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[A-z0-9\-]+');

