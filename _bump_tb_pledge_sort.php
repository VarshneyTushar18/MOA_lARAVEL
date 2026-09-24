<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$section = App\Models\PageSection::findOrFail(53);
$updated = $section->media()->where('type', 'video')->update([
    'sort_order' => \Illuminate\Support\Facades\DB::raw('sort_order + 100'),
]);
echo "Bumped sort_order +100 for $updated existing videos\n";
echo 'total_videos='.$section->media()->where('type', 'video')->count()."\n";
