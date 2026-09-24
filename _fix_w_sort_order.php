<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$map = [
    45 => 1, // W1
    46 => 2, // W2
    47 => 3, // W3
    48 => 4, // W4
    49 => 5, // W5
];

foreach ($map as $id => $order) {
    $section = App\Models\PageSection::find($id);
    if (! $section) {
        echo "Missing section $id\n";
        continue;
    }
    $section->sort_order = $order;
    $section->save();
    echo "Updated {$section->title} (id=$id) sort_order=$order\n";
}

$parent = App\Models\PageSection::find(44);
if ($parent) {
    echo "\nOrder now:\n";
    foreach ($parent->subsections()->orderBy('sort_order')->orderBy('id')->get(['id', 'title', 'sort_order']) as $s) {
        echo "{$s->id} {$s->title} order={$s->sort_order}\n";
    }
}
