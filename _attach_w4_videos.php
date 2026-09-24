<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$section = App\Models\PageSection::findOrFail(48);
$up = '/home/sites/41b/b/ba690bc503/moa_deploy_upload';
$destDir = storage_path('app/public/page_sections/videos');
if (! is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$files = [
    'section_video_1.mp4' => '1. 20230414_093406_Video.mp4',
    'section_video_2.mp4' => '2. 20230414_095817_Video.mp4',
    'section_video_3.mp4' => '3. 20230414_102137.mp4',
    'section_video_4.mp4' => '4. 20230907_093204.mp4',
];

foreach ($files as $remote => $original) {
    $src = $up.'/'.$remote;
    if (! file_exists($src)) {
        echo "Missing $src\n";
        exit(1);
    }
    $stored = 'page_sections/videos/'.uniqid('w4_', true).'_'.preg_replace('/[^A-Za-z0-9._-]+/', '_', $original);
    $full = storage_path('app/public/'.$stored);
    copy($src, $full);
    $media = $section->media()->create(['type' => 'video', 'file_path' => $stored, 'title' => $original]);
    echo "Attached media id={$media->id} path=$stored\n";
}

echo 'DONE section='.$section->id.' videos='.$section->media()->where('type', 'video')->count()."\n";
