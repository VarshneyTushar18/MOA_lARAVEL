<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$images = App\Models\PageSection::findOrFail(53)->images()->orderBy('id')->get();
echo 'total='.$images->count()."\n";
echo 'id_range='.$images->first()->id.'-'.$images->last()->id."\n\n";

$byDate = [];
foreach ($images as $img) {
    $day = $img->created_at?->format('Y-m-d') ?? 'unknown';
    $byDate[$day] = ($byDate[$day] ?? 0) + 1;
}
echo "by_created_date:\n";
foreach ($byDate as $day => $count) {
    echo "  $day: $count\n";
}

// chunk by 100 ids
echo "\nby_id_chunks:\n";
$chunks = $images->chunk(100);
foreach ($chunks as $i => $chunk) {
    $first = $chunk->first();
    $last = $chunk->last();
    echo '  chunk '.($i+1).': ids '.$first->id.'-'.$last->id.' ('.$chunk->count().') created '.$first->created_at?->format('Y-m-d H:i').' to '.$last->created_at?->format('Y-m-d H:i')."\n";
}
