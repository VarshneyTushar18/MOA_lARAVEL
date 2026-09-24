<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$section = App\Models\PageSection::findOrFail(44);
$section->section_key = 'training_survey';
$section->save();

echo "Fixed section {$section->id}: key={$section->section_key}, title={$section->title}\n";
