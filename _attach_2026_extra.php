<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$section = App\Models\PageSection::findOrFail(53);
$src = '/home/sites/41b/b/ba690bc503/moa_deploy_upload/tb_pledge_2026_extra_4.mp4';
$stored = 'page_sections/videos/' . uniqid('tb2026x_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', '2.(b)_WhatsApp Video 2026-03-24 at 11.03.48.mp4');
copy($src, storage_path('app/public/' . $stored));
$media = $section->media()->create(['type'=>'video','file_path'=>$stored,'title'=>'2.(b)_WhatsApp Video 2026-03-24 at 11.03.48.mp4','sort_order'=>2021]);
echo "Attached id={$media->id} sort=2021\n";
echo "DONE videos=".$section->media()->where('type','video')->count()."\n";
