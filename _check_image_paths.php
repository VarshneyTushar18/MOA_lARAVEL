<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$images = App\Models\PageSection::findOrFail(53)->images()->orderBy('id')->take(5)->get();
foreach ($images as $img) {
    $path = storage_path('app/public/'.$img->image);
    echo $img->id.' '.$img->image.' exists='.(file_exists($path)?'yes':'no')."\n";
}
