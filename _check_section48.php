<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = App\Models\PageSection::find(48);
echo $s->title.' images='.$s->images()->count().PHP_EOL;
