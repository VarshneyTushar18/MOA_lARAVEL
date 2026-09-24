<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sectionId = 48;
$batch = $argv[1] ?? '';
if ($batch === '') { fwrite(STDERR, "Batch id required\n"); exit(1); }

$up = '/home/sites/41b/b/ba690bc503/moa_deploy_upload';
$manifestPath = "$up/w1_images_{$batch}.json";
$extractDir = "$up/w1_images_{$batch}";
$tarPath = "$up/w1_images_{$batch}.tar";

if (!file_exists($manifestPath)) { echo "Missing manifest $manifestPath\n"; exit(1); }
if (!file_exists($tarPath)) { echo "Missing tar $tarPath\n"; exit(1); }

if (!is_dir($extractDir)) mkdir($extractDir, 0755, true);
foreach (glob($extractDir . '/*') ?: [] as $old) {
    if (is_file($old)) unlink($old);
}
$cmd = 'tar -xf ' . escapeshellarg($tarPath) . ' -C ' . escapeshellarg($extractDir);
exec($cmd, $out, $code);
if ($code !== 0) { echo "tar extract failed ($code)\n"; exit(1); }

$section = App\Models\PageSection::findOrFail($sectionId);
$names = json_decode(file_get_contents($manifestPath), true);
if (!is_array($names)) { echo "Bad manifest\n"; exit(1); }

$destDir = storage_path('app/public/page_sections/images');
if (!is_dir($destDir)) mkdir($destDir, 0755, true);

$existing = $section->images()->pluck('image')->all();
$existingBase = array_map(static fn ($p) => strtolower(basename((string) $p)), $existing);

$added = 0;
$skipped = 0;
foreach ($names as $name) {
    $base = basename((string) $name);
    if (in_array(strtolower($base), $existingBase, true)) {
        $skipped++;
        continue;
    }
    $src = $extractDir . '/' . $base;
    if (!is_file($src)) {
        echo "Missing extracted file: $src\n";
        continue;
    }
    $stored = 'page_sections/images/' . uniqid('w1_', true) . '_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $base);
    $full = storage_path('app/public/' . $stored);
    copy($src, $full);
    $section->images()->create(['image' => $stored]);
    $existingBase[] = strtolower($base);
    $added++;
    if ($added % 25 === 0) echo "Added $added...\n";
}

echo "DONE batch=$batch section={$section->id} added=$added skipped=$skipped total=" . $section->images()->count() . "\n";
