<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = App\Models\PageSection::with(['parent', 'media', 'images'])->find(45);
if (! $s) {
    echo "Section 45 not found\n";
    exit(1);
}

echo "id={$s->id} key={$s->section_key} title={$s->title} parent_id={$s->parent_id}\n";
echo "parent=".($s->parent?->section_key ?? 'none')."\n";
echo "videos=".json_encode($s->videos)."\n";
echo "image=".($s->image ?? '')."\n";
foreach ($s->media as $m) {
    echo "media id={$m->id} type={$m->type} path=".($m->file_path ?? '')." youtube=".($m->youtube_url ?? '')."\n";
}
