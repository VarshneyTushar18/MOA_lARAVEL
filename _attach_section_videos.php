<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$section = App\Models\PageSection::findOrFail(48);
$up = '/home/sites/41b/b/ba690bc503/moa_deploy_upload';
$destDir = storage_path('app/public/page_sections/videos');
if (!is_dir($destDir)) mkdir($destDir, 0755, true);
$src = '$up/section_video_1.mp4';
if (!file_exists($src)) { echo "Missing $src\n"; exit(1); }
$name = '1. 20230414_093406_Video.mp4';
$stored = 'page_sections/videos/' . uniqid('w1_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create(['type' => 'video', 'file_path' => $stored]);
echo "Attached media id={$media->id} path=$stored\n";
$src = '$up/section_video_2.mp4';
if (!file_exists($src)) { echo "Missing $src\n"; exit(1); }
$name = '2. 20230414_095817_Video.mp4';
$stored = 'page_sections/videos/' . uniqid('w1_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create(['type' => 'video', 'file_path' => $stored]);
echo "Attached media id={$media->id} path=$stored\n";
$src = '$up/section_video_3.mp4';
if (!file_exists($src)) { echo "Missing $src\n"; exit(1); }
$name = '3. 20230414_102137.mp4';
$stored = 'page_sections/videos/' . uniqid('w1_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create(['type' => 'video', 'file_path' => $stored]);
echo "Attached media id={$media->id} path=$stored\n";
$src = '$up/section_video_4.mp4';
if (!file_exists($src)) { echo "Missing $src\n"; exit(1); }
$name = '4. 20230907_093204.mp4';
$stored = 'page_sections/videos/' . uniqid('w1_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
$full = storage_path('app/public/' . $stored);
copy($src, $full);
$media = $section->media()->create(['type' => 'video', 'file_path' => $stored]);
echo "Attached media id={$media->id} path=$stored\n";
echo "DONE section={$section->id} key={$section->section_key}\n";