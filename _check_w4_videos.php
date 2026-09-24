<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = App\Models\PageSection::find(48);
echo 'W4 videos='.$s->media()->where('type', 'video')->count().PHP_EOL;
foreach ($s->media()->where('type', 'video')->orderBy('id')->get(['id', 'file_path', 'created_at']) as $m) {
    echo $m->id.' '.$m->file_path.' '.$m->created_at.PHP_EOL;
}
