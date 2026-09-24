<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$s = App\Models\PageSection::find(53);
echo 'videos='.$s->media()->where('type','video')->count()."\n";
echo 'sort_1000_plus='.$s->media()->where('type','video')->where('sort_order','>=',1000)->count()."\n";
