<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$section = App\Models\PageSection::findOrFail(53);
$up = '/home/sites/41b/b/ba690bc503/moa_deploy_upload';
$src = $up . '/tb_pledge_2025_40.mp4';
if (!file_exists($src)) { echo "Missing $src\n"; exit(1); }
$destDir = storage_path('app/public/page_sections/videos');
if (!is_dir($destDir)) mkdir($destDir, 0755, true);
$name = 'V-8_ WhatsApp Video 2025-10-02 at 20.07.mp4';
$stored = 'page_sections/videos/' . uniqid('tb2025_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create([
    'type' => 'video',
    'file_path' => $stored,
    'title' => $name,
    'sort_order' => 1040,
]);
echo "Attached media id={$media->id} sort=1040 path=$stored size=" . filesize($full) . "\n";
echo "DONE videos=" . $section->media()->where('type', 'video')->count() . "\n";
