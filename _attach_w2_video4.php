<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$section = App\Models\PageSection::findOrFail(46);
$src = '/home/sites/41b/b/ba690bc503/moa_deploy_upload/w2_video_4.mp4';
if (!file_exists($src)) {
    echo "Missing $src\n";
    exit(1);
}

$name = '4. 20230315_152313_Video.mp4';
$stored = 'page_sections/videos/' . uniqid('w2_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create([
    'type' => 'video',
    'file_path' => $stored,
    'title' => $name,
]);
echo "Attached media id={$media->id} path=$stored\n";
echo "DONE section={$section->id} key={$section->section_key} videos=" . $section->media()->where('type', 'video')->count() . "\n";
