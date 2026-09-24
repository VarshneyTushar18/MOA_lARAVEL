<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$img = App\Models\PageSection::findOrFail(53)
    ->images()
    ->where('sort_order', '>=', 1000)
    ->where('sort_order', '<', 2000)
    ->first();

$path = $img->image;
$storage = storage_path('app/public/'.$path);
$public = public_path('storage/'.$path);
$link = public_path('storage');

echo "db_path={$path}\n";
echo "storage_exists=".(file_exists($storage) ? 'yes' : 'no')."\n";
echo "public_storage_exists=".(file_exists($public) ? 'yes' : 'no')."\n";
echo "storage_link=".(is_link($link) ? readlink($link) : (is_dir($link) ? 'dir-not-link' : 'missing'))."\n";
echo "url=/storage/{$path}\n";
