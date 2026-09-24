<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$section = App\Models\PageSection::findOrFail(130);
$up = '/home/sites/41b/b/ba690bc503/moa_deploy_upload';
$src = $up . '/opd_video_56.mp4';
if (!file_exists($src)) { echo "Missing $src\n"; exit(1); }
$destDir = storage_path('app/public/page_sections/videos');
if (!is_dir($destDir)) mkdir($destDir, 0755, true);
$name = 'VID_20260317_114151860.mp4';
$stored = 'page_sections/videos/' . uniqid('opd_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create([
    'type' => 'video',
    'file_path' => $stored,
    'title' => pathinfo($name, PATHINFO_FILENAME),
    'sort_order' => 56,
]);
echo "Attached media id={$media->id} path=$stored size=" . filesize($full) . "\n";
echo "DONE videos=" . $section->media()->where('type', 'video')->count() . "\n";
