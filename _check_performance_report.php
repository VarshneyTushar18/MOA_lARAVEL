<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$page = App\Models\Page::where('slug', 'performance_report')
    ->with(['sections.media', 'sections.images'])
    ->firstOrFail();

foreach ($page->sections as $section) {
    echo $section->id.'|'.$section->section_key.'|'.$section->title
        .'|videos='.$section->media->where('type', 'video')->count()
        .'|pdfs='.$section->media->where('type', 'pdf')->count()
        .'|images='.$section->images->count()
        ."\n";
}
