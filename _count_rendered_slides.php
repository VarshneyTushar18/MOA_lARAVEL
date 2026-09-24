<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$page = App\Models\Page::where('slug', 'acsm_iec')->orWhere('title', 'like', '%ACSM%')->first();
if (! $page) {
    $page = App\Models\Page::find(5);
}

$html = view('pages.acsm_iec', ['page' => $page->load('sections.images', 'sections.media')])->render();
preg_match_all('/tbPledge53-images-2025.*?<\/div>\s*<\/div>\s*<\/div>/s', $html, $blocks);
$block = $blocks[0][0] ?? '';
$slides = substr_count($block, 'swiper-slide');
$imgs = substr_count($block, '<img ');
echo "2025_block_slides={$slides}\n";
echo "2025_block_imgs={$imgs}\n";
if (preg_match('/<img[^>]+src="([^"]+)"/', $block, $m)) {
    echo "first_src={$m[1]}\n";
}
