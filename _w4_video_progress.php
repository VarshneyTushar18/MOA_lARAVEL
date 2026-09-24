<?php

$staging = '/home/sites/41b/b/ba690bc503/moa_deploy_upload';
$storage = '/home/sites/41b/b/ba690bc503/MOA_lARAVEL/storage/app/public/page_sections/videos';

$sources = [
    1 => ['file' => 'section_video_1.mp4', 'label' => '1. 20230414_093406_Video.mp4', 'match' => '1._20230414_093406'],
    2 => ['file' => 'section_video_2.mp4', 'label' => '2. 20230414_095817_Video.mp4', 'match' => '2._20230414_095817'],
    3 => ['file' => 'section_video_3.mp4', 'label' => '3. 20230414_102137.mp4', 'match' => '3._20230414_102137'],
    4 => ['file' => 'section_video_4.mp4', 'label' => '4. 20230907_093204.mp4', 'match' => '4._20230907_093204'],
];

$totalSrc = 0;
$totalDone = 0;

echo "W4 video upload progress\n";
echo str_repeat('=', 50)."\n";
echo "Step 1 — PC to server (SCP): 100% (all 4 files on server)\n\n";
echo "Step 2 — Attach to site storage:\n";

foreach ($sources as $num => $info) {
    $srcPath = $staging.'/'.$info['file'];
    $srcSize = file_exists($srcPath) ? filesize($srcPath) : 0;
    $totalSrc += $srcSize;

    $dstSize = 0;
    foreach (glob($storage.'/w4_*'.$info['match'].'*') ?: [] as $dst) {
        $dstSize = max($dstSize, filesize($dst) ?: 0);
    }

    $totalDone += min($dstSize, $srcSize);
    $pct = $srcSize > 0 ? min(100, ($dstSize / $srcSize) * 100) : 0;
    $status = $pct >= 99 ? 'DONE' : number_format($pct, 1).'%';

    printf(
        "  Video %d: %6.2f / %6.2f GB  [%s]  %s\n",
        $num,
        $dstSize / 1024 ** 3,
        $srcSize / 1024 ** 3,
        $status,
        $info['label']
    );
}

$overall = $totalSrc > 0 ? ($totalDone / $totalSrc) * 100 : 0;
echo str_repeat('-', 50)."\n";
printf("Overall attach: %.1f%%  (%.2f / %.2f GB)\n", $overall, $totalDone / 1024 ** 3, $totalSrc / 1024 ** 3);

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$count = App\Models\PageSection::find(48)?->media()->where('type', 'video')->count() ?? 0;
echo "Videos saved in W4 database: {$count} / 4\n";
