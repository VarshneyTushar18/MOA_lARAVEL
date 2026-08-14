<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionImage;
use App\Models\PageSectionMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportPenDriveMedia extends Command
{
    protected $signature = 'moa:import-pendrive
                            {--skip-video : Skip the large launch video}
                            {--only-missing : Only fill sections that are short vs pen-drive counts}
                            {--root= : Override pen drive root path}';

    protected $description = 'Replace page media with Pen Drive Data files';

    public function handle(): int
    {
        $root = $this->option('root') ?: env('PEN_DRIVE_ROOT', 'E:\\MOA DATABASE\\Pen Drive Data');
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $onlyMissing = (bool) $this->option('only-missing');

        if (! is_dir($root)) {
            $this->error("Pen drive folder not found: {$root}");
            return self::FAILURE;
        }

        $this->info("Using pen drive root: {$root}".($onlyMissing ? ' (only missing)' : ''));

        if (! $onlyMissing) {
            $this->seedHomeFallbackImages();
        }

        $about = $this->page('about-us');
        $fact = $this->page('factsheet') ?? $this->page('facesheet');
        $acsm = $this->page('acsm_iec');
        $best = $this->page('best_practices');
        $home = $this->page('home');

        $ntpc = $this->section($about, 'ntpc');
        if (! $onlyMissing || $this->imageCount($ntpc) < 21) {
            $this->replaceImages(
                $ntpc,
                $this->files($this->pd($root, '3. ABOUT US/4. photographs'), ['jpg', 'jpeg', 'png', 'webp']),
                'page_sections/images/about-ntpc'
            );
        }
        $scheme = $this->section($about, 'scheme');
        if (! $onlyMissing || $this->pdfCount($scheme) < 1) {
            $this->addPdfs(
                $scheme,
                [$this->pd($root, '3. ABOUT US/2. Details of MOA Schemes/2.(b)_Ayurswasthya_Refrence Document.pdf')],
                'page_sections/pdfs/scheme',
                true
            );
        }

        $training = $this->section($fact, 'training_survey');
        $w1 = $this->child($training, 'W1');
        if (! $onlyMissing || $this->imageCount($w1) < 18) {
            $this->replaceImages(
                $w1,
                $this->files($this->pd($root, '4. FACT Sheet/1. Training and workshop Program/W-1'), ['jpg', 'jpeg', 'png']),
                'page_sections/images/factsheet-w1'
            );
        }
        $w2 = $this->child($training, 'W2');
        if (! $onlyMissing || $this->imageCount($w2) < 25) {
            $this->replaceImages(
                $w2,
                $this->files($this->pd($root, '4. FACT Sheet/1. Training and workshop Program/W-2_to be replaced'), ['jpg', 'jpeg', 'png']),
                'page_sections/images/factsheet-w2'
            );
        }
        $w3 = $this->child($training, 'W3');
        if (! $onlyMissing || $this->imageCount($w3) < 6) {
            $this->replaceImages(
                $w3,
                $this->files($this->pd($root, '4. FACT Sheet/1. Training and workshop Program/W-3'), ['jpg', 'jpeg', 'png']),
                'page_sections/images/factsheet-w3'
            );
        }
        $w4 = $this->child($training, 'W4');
        if (! $onlyMissing || $this->imageCount($w4) < 44) {
            $this->replaceImages(
                $w4,
                $this->files($this->pd($root, '4. FACT Sheet/1. Training and workshop Program/W-4'), ['jpg', 'jpeg', 'png']),
                'page_sections/images/factsheet-w4'
            );
        }
        if (! $onlyMissing || $this->youtubeCount($w4) < 1) {
            $this->mergeYoutubeFromPdf(
                $w4,
                $this->pd($root, '4. FACT Sheet/1. Training and workshop Program/W-4/WEBLINK.pdf')
            );
        }
        $w5 = $this->child($training, 'W5');
        if (! $onlyMissing || $this->youtubeCount($w5) < 1) {
            $this->mergeYoutubeFromPdf(
                $w5,
                $this->pd($root, '4. FACT Sheet/1. Training and workshop Program/W-5-6/WEBLINK.pdf')
            );
        }
        $survey = $this->section($fact, 'survey_data');
        if (! $onlyMissing || $this->pdfCount($survey) < 1) {
            $this->addPdfs(
                $survey,
                [$this->pd($root, '4. FACT Sheet/2. Survey Data/Screening-Performa-QR-Code-Google-Form-Link.pdf')],
                'page_sections/pdfs/survey',
                true
            );
        }

        if (! $onlyMissing) {
            $this->replaceImages(
                $this->section($acsm, 'pledge'),
                $this->files($this->pd($root, '5.  ACSM and IEC/1. Pledge'), ['jpg', 'jpeg', 'png']),
                'page_sections/images/acsm-pledge'
            );
            $this->replacePdfs(
                $this->section($acsm, 'pledge'),
                $this->files($this->pd($root, '5.  ACSM and IEC/1. Pledge'), ['pdf']),
                'page_sections/pdfs/acsm-pledge'
            );
            $this->replacePdfs(
                $this->section($acsm, 'diet_chart'),
                $this->files($this->pd($root, '5.  ACSM and IEC/2. Diet Chart'), ['pdf']),
                'page_sections/pdfs/acsm-diet'
            );
            $this->replacePdfs(
                $this->section($acsm, 'daily_regimen'),
                $this->files($this->pd($root, '5.  ACSM and IEC/3. Daily Regimen'), ['pdf']),
                'page_sections/pdfs/acsm-regimen'
            );
            $this->replacePdfs(
                $this->section($acsm, 'pamphlets'),
                $this->files($this->pd($root, '5.  ACSM and IEC/4. Pamphlets'), ['pdf']),
                'page_sections/pdfs/acsm-pamphlets'
            );
        }

        $posterDir = $this->pd($root, '5.  ACSM and IEC/6. Poster Banners and Wall Stics');
        $posters = $this->section($acsm, 'poster_banners_wall_stickers');
        if (! $onlyMissing || $this->imageCount($posters) < 40 || $this->pdfCount($posters) < 1) {
            $posterImages = array_values(array_filter(
                $this->files($posterDir, ['jpg', 'jpeg', 'png', 'webp']),
                function ($path) {
                    $name = strtolower(basename($path));
                    if (preg_match('/^(38|39|40|41|42|43)\./', $name)) {
                        return false;
                    }
                    if (str_contains($name, '14.(b)_img_2769') || str_contains($name, '14.(b)_img_2769.jpg')) {
                        return false;
                    }
                    return ! str_contains($name, '14.(b)');
                }
            ));
            $this->replaceImages($posters, $posterImages, 'page_sections/images/acsm-posters');
            $this->replacePdfs(
                $posters,
                $this->files($posterDir, ['pdf']),
                'page_sections/pdfs/acsm-posters'
            );
        }

        if (! $onlyMissing) {
            $promoDir = $this->pd($root, '5.  ACSM and IEC/7. Promotnal materials');
            $this->replaceImages(
                $this->section($acsm, 'promotional_material'),
                $this->files($promoDir, ['jpg', 'jpeg', 'png']),
                'page_sections/images/acsm-promo'
            );
            $this->replacePdfs(
                $this->section($acsm, 'promotional_material'),
                $this->files($promoDir, ['pdf']),
                'page_sections/pdfs/acsm-promo'
            );
        }

        $launch = $this->section($acsm, 'launch_video');
        if ($this->option('skip-video')) {
            $this->warn('Skipped launch video (--skip-video).');
        } else {
            $this->replaceVideos(
                $launch,
                [$this->pd($root, '5.  ACSM and IEC/8. Lanch Videos/1.Infrastructure_Lanuch_ video.mp4')],
                'page_sections/videos/acsm-launch'
            );
        }

        if (! $onlyMissing) {
            $this->replaceImages(
                $this->section($acsm, 'tb_pledge'),
                $this->files($this->pd($root, '5.  ACSM and IEC/9. Tb Pledge and Awareness Videos'), ['jpg', 'jpeg', 'png']),
                'page_sections/images/acsm-tb-pledge'
            );
            $this->replaceImages(
                $this->section($acsm, 'logo'),
                $this->files($this->pd($root, '5.  ACSM and IEC/12. Logo'), ['jpg', 'jpeg', 'png']),
                'page_sections/images/acsm-logo'
            );
            $this->replaceImages(
                $this->section($best, 'photos'),
                $this->files($this->pd($root, '7. Best Practises/3. Photos'), ['jpg', 'jpeg', 'png']),
                'page_sections/images/best-photos'
            );
            $this->replaceVideos(
                $this->section($best, 'patient_videos'),
                [$this->pd($root, '7. Best Practises/2. Patient Appraisal Videos/2. Minstry of  Ayush_Doorderson_6.mp4')],
                'page_sections/videos/best-patient'
            );
        }

        $pm = $home ? $this->section($home, 'pm_yojna') : null;
        if ($home && (! $onlyMissing || $this->pdfCount($pm) < 1)) {
            $this->addPdfs(
                $pm,
                [$this->pd($root, '1. Front Page Content/Website LTBI.pdf')],
                'page_sections/pdfs/home-pm',
                true
            );
        }

        $this->info('Pen drive media import complete.');
        $this->info('Open: '.$this->previewUrls());

        return self::SUCCESS;
    }

    private function previewUrls(): string
    {
        $base = rtrim(config('app.url') ?: 'http://127.0.0.1:8000', '/');
        return implode(' | ', [
            $base.'/',
            $base.'/about',
            $base.'/factsheet',
            $base.'/acsm_iec',
            $base.'/best_practices',
        ]);
    }

    private function page(string $slug): ?Page
    {
        $page = Page::where('slug', $slug)->first();
        if (! $page) {
            $this->warn("Page missing: {$slug}");
        }
        return $page;
    }

    private function section(?Page $page, string $key): ?PageSection
    {
        if (! $page) {
            return null;
        }
        $section = $page->sections()->where('section_key', $key)->whereNull('parent_id')->first()
            ?: $page->sections()->where('section_key', $key)->first();
        if (! $section) {
            $this->warn("Section missing on {$page->slug}: {$key}");
        }
        return $section;
    }

    private function child(?PageSection $parent, string $key): ?PageSection
    {
        if (! $parent) {
            return null;
        }
        $child = PageSection::where('parent_id', $parent->id)->where('section_key', $key)->first();
        if (! $child) {
            $this->warn("Child section missing under {$parent->section_key}: {$key}");
        }
        return $child;
    }

    private function pd(string $root, string $relative): string
    {
        return rtrim($root, '/').'/'.str_replace('\\', '/', ltrim($relative, '/'));
    }

    private function imageCount(?PageSection $section): int
    {
        return $section ? $section->images()->count() : 0;
    }

    private function pdfCount(?PageSection $section): int
    {
        return $section ? $section->media()->where('type', 'pdf')->count() : 0;
    }

    private function videoCount(?PageSection $section): int
    {
        return $section ? $section->media()->where('type', 'video')->count() : 0;
    }

    private function youtubeCount(?PageSection $section): int
    {
        return $section ? $section->media()->where('type', 'youtube')->count() : 0;
    }

    private function files(string $dir, array $exts): array
    {
        if (! is_dir($dir)) {
            $this->warn("Folder missing: {$dir}");
            return [];
        }
        $exts = array_map('strtolower', $exts);
        $out = [];
        foreach (File::files($dir) as $file) {
            $ext = strtolower($file->getExtension());
            $name = $file->getFilename();
            if (str_starts_with($name, '~$')) {
                continue;
            }
            if (in_array($ext, $exts, true)) {
                $out[] = $file->getPathname();
            }
        }
        natcasesort($out);
        return array_values($out);
    }

    private function replaceImages(?PageSection $section, array $paths, string $destDir): void
    {
        if (! $section) {
            return;
        }
        if (! $paths) {
            $this->warn("No image files for {$section->section_key}; leaving existing media.");
            return;
        }
        foreach ($section->images as $image) {
            if ($image->image) {
                Storage::disk('public')->delete($image->image);
            }
            $image->delete();
        }
        $count = 0;
        foreach ($paths as $path) {
            if (! is_file($path)) {
                continue;
            }
            $relative = $this->storeFile($path, $destDir);
            $section->images()->create(['image' => $relative]);
            $count++;
        }
        $this->line("Images {$section->section_key}: {$count}");
    }

    private function replacePdfs(?PageSection $section, array $paths, string $destDir): void
    {
        if (! $section) {
            return;
        }
        if (! $paths) {
            $this->warn("No PDF files for {$section->section_key}; leaving existing media.");
            return;
        }
        $section->media()->where('type', 'pdf')->get()->each(function (PageSectionMedia $media) {
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();
        });
        $this->addPdfs($section, $paths, $destDir, false);
    }

    private function addPdfs(?PageSection $section, array $paths, string $destDir, bool $skipIfExists): void
    {
        if (! $section) {
            return;
        }
        $count = 0;
        foreach ($paths as $path) {
            if (! is_file($path)) {
                continue;
            }
            $name = basename($path);
            if ($skipIfExists && $section->media()->where('type', 'pdf')->get()->contains(function ($m) use ($name) {
                return str_contains((string) $m->file_path, pathinfo($name, PATHINFO_FILENAME));
            })) {
                continue;
            }
            $relative = $this->storeFile($path, $destDir);
            $section->media()->create([
                'type' => 'pdf',
                'file_path' => $relative,
            ]);
            $count++;
        }
        $this->line("PDFs {$section->section_key}: {$count}");
    }

    private function replaceVideos(?PageSection $section, array $paths, string $destDir): void
    {
        if (! $section) {
            return;
        }
        if (! $paths) {
            $this->warn("No video files for {$section->section_key}; leaving existing media.");
            return;
        }
        $section->media()->where('type', 'video')->get()->each(function (PageSectionMedia $media) {
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();
        });
        $count = 0;
        foreach ($paths as $path) {
            if (! is_file($path)) {
                continue;
            }
            $relative = $this->storeFile($path, $destDir);
            $section->media()->create([
                'type' => 'video',
                'file_path' => $relative,
            ]);
            $count++;
        }
        $this->line("Videos {$section->section_key}: {$count}");
    }

    private function mergeYoutubeFromPdf(?PageSection $section, string $pdfPath): void
    {
        if (! $section || ! is_file($pdfPath)) {
            return;
        }
        $raw = @file_get_contents($pdfPath) ?: '';
        preg_match_all('#https?://(?:www\.)?(?:youtube\.com|youtu\.be)/[^\s<>"\']+#i', $raw, $matches);
        $urls = [];
        foreach ($matches[0] ?? [] as $url) {
            $url = rtrim($url, '.,);');
            $urls[$url] = $url;
        }
        if (! $urls) {
            $this->warn("No YouTube URLs found in ".basename($pdfPath));
            return;
        }
        if (Schema::hasColumn('page_sections', 'videos')) {
            $existing = $section->getAttributes()['videos'] ?? null;
            if (is_array($existing)) {
                $current = $existing;
            } else {
                $current = $existing ? (json_decode((string) $existing, true) ?: []) : [];
            }
            if (! is_array($current)) {
                $current = [];
            }
            $merged = array_values(array_unique(array_merge($current, array_values($urls))));
            DB::table('page_sections')->where('id', $section->id)->update([
                'videos' => json_encode($merged),
                'updated_at' => now(),
            ]);
        }
        foreach ($urls as $url) {
            $exists = $section->media()->where('type', 'youtube')->where('youtube_url', $url)->exists();
            if (! $exists) {
                $section->media()->create([
                    'type' => 'youtube',
                    'youtube_url' => $url,
                ]);
            }
        }
        $this->line("YouTube {$section->section_key}: ".count($urls)." link(s)");
    }

    private function storeFile(string $absolutePath, string $destDir): string
    {
        $safe = Str::slug(pathinfo($absolutePath, PATHINFO_FILENAME), '_');
        if ($safe === '') {
            $safe = 'file';
        }
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $relative = trim($destDir, '/').'/'.$safe.'.'.$ext;
        $i = 2;
        while (Storage::disk('public')->exists($relative)) {
            $relative = trim($destDir, '/').'/'.$safe.'_'.$i.'.'.$ext;
            $i++;
        }
        $absoluteDest = Storage::disk('public')->path($relative);
        File::ensureDirectoryExists(dirname($absoluteDest));
        File::copy($absolutePath, $absoluteDest);
        return $relative;
    }

    private function seedHomeFallbackImages(): void
    {
        $home = Page::where('slug', 'home')->first();
        if (! $home) {
            return;
        }

        $map = [
            'Tb.jpg' => public_path('assets/images/Tb.jpg'),
            'pm-yojna.webp' => public_path('assets/images/pm-yojna.webp'),
            'pm-yojna-banner.webp' => public_path('assets/images/pm-yojna-banner.webp'),
        ];
        foreach ($map as $name => $src) {
            if (is_file($src) && ! Storage::disk('public')->exists($name)) {
                Storage::disk('public')->put($name, File::get($src));
            }
        }

        $ministry = $this->section($home, 'ministry');
        if ($ministry && $ministry->images()->count() === 0) {
            foreach (['about-ministry-1.png', 'about-ministry-2.png', 'about-ministry-3.png'] as $file) {
                $src = public_path('assets/images/'.$file);
                if (! is_file($src)) {
                    continue;
                }
                $relative = 'page_sections/images/home-ministry/'.$file;
                Storage::disk('public')->put($relative, File::get($src));
                $ministry->images()->create(['image' => $relative]);
            }
        }

        $aiia = $this->section($home, 'aiia');
        if ($aiia && ! $aiia->image) {
            $src = public_path('assets/images/about-aiia.webp');
            if (is_file($src)) {
                Storage::disk('public')->put('page_sections/images/home-aiia/about-aiia.webp', File::get($src));
                $aiia->image = 'page_sections/images/home-aiia/about-aiia.webp';
                $aiia->save();
            }
        }

        $hero = $this->section($home, 'hero_banner');
        if ($hero && $hero->images()->count() === 0) {
            foreach (['pm-yojna-banner.webp', 'ltbi-1.webp', 'ltbi-2.webp', 'ltbi-3.webp'] as $file) {
                $src = public_path('assets/images/'.$file);
                if (! is_file($src)) {
                    continue;
                }
                $relative = 'page_sections/images/home-hero/'.$file;
                Storage::disk('public')->put($relative, File::get($src));
                $hero->images()->create(['image' => $relative]);
            }
        }
    }
}
