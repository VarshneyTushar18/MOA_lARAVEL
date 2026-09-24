<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\PageSection::whereIn('id', [45, 46, 47, 48])->get() as $s) {
    echo $s->id.' '.$s->title
        .' videos='.$s->media()->where('type', 'video')->count()
        .' images='.$s->images()->count().PHP_EOL;
}

$recent = App\Models\PageSectionMedia::where('type', 'video')
    ->orderByDesc('id')
    ->limit(5)
    ->get(['id', 'page_section_id', 'file_path', 'created_at']);

echo "--- recent video media ---\n";
foreach ($recent as $m) {
    echo $m->id.' section='.$m->page_section_id.' '.$m->file_path.' '.$m->created_at.PHP_EOL;
}
