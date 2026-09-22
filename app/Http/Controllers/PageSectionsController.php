<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionHighlightItem;
use App\Models\PageSectionImage;
use App\Models\PageSectionMedia;
use App\Services\CompressedUploadStorage;
use App\Services\HeroCarouselService;
use App\Support\UploadLimits;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PageSectionsController extends Controller
{
    // List sections
    public function list(Page $page)
    {
        $sections = $this->sectionsInDisplayOrder($page)
            ->load(['images', 'media', 'parent', 'subsections'])
            ->filter(fn (PageSection $section) => ! $this->isHiddenAdminSection($page, $section))
            ->values();

        return view('pages_console.sections.list', [
            'page' => $page,
            'sections' => $sections,
            'galleryAdminUrl' => $page->slug === 'home' ? $this->galleryAdminUrl() : null,
        ]);
    }

    // Show add form
    public function addForm(Page $page)
    {
        return view('pages_console.sections.add', [
            'page' => $page,
        ]);
    }

    // Add section
    public function add(Page $page)
    {
        $this->stripIrrelevantSectionInput();

        $attributes = request()->validate(
            array_merge($this->sectionBaseValidationRules(), $this->marqueeValidationRules(), $this->videoUploadValidationRules()),
            $this->videoUploadValidationMessages()
        );

        $this->assertUploadsNotBlockedByPhp(request());

        if ($page->slug === 'home' && ($attributes['section_key'] ?? '') === 'marquee_link') {
            throw ValidationException::withMessages([
                'section_key' => 'Do not add marquee_link here. Edit the home_marquee section and use Add Link.',
            ]);
        }

        $this->assertNotDuplicateHomeGallery($page, $attributes['section_key'] ?? '');

        $section = new PageSection;
        $section->page_id = $page->id;
        $section->section_key = $attributes['section_key'];
        $section->title = $attributes['title'] ?? null;
        $section->description = $attributes['description'] ?? null;
        $isHomeMarquee = $page->slug === 'home' && $attributes['section_key'] === 'home_marquee';
        $section->text_color = $isHomeMarquee ? ($attributes['text_color'] ?? null) : null;
        $section->bg_color = $isHomeMarquee ? ($attributes['bg_color'] ?? null) : null;
        $requestedOrder = (int) ($attributes['sort_order'] ?? 0);
        $section->sort_order = 0;
        $section->parent_id = $attributes['parent_id'] ?? null;

        if (request()->hasFile('image')) {
            $section->image = CompressedUploadStorage::storeImage(request()->file('image'), 'page_sections', 'public');
        }

        $section->save();

        if ($this->isFactsheetHighlightsChild($page, $attributes) && request()->hasFile('videos')) {
            $this->validateHighlightVideoDurations(request()->file('videos'));
        }

        if ($this->isFactsheetHighlightsSection($page, $attributes['section_key'] ?? null)) {
            $this->syncHighlightItems(request(), $section);
        }

        if ($this->isHomeMarqueeSection($page, $attributes['section_key'] ?? null, $section)) {
            $this->syncMarqueeLinks(request(), $page, $section);
        }

        // Multiple images
        if (request()->hasFile('images')) {
            foreach (request()->file('images') as $file) {
                $path = CompressedUploadStorage::storeImage($file, 'page_sections/images', 'public');
                $image = $section->images()->create(['image' => $path]);
                if ($this->heroCarousel()->isHeroBanner($section, $page)) {
                    $this->heroCarousel()->appendToOrder($section, ['k' => 'image', 'id' => $image->id]);
                }
            }
        }

        // PDFs
        if (request()->hasFile('pdfs')) {
            foreach (request()->file('pdfs') as $pdf) {
                $path = $pdf->store('page_sections/pdfs', 'public');
                $section->media()->create([
                    'type' => 'pdf',
                    'file_path' => $path,
                    'title' => request('new_pdf_title'),
                    'description' => request('new_pdf_description'),
                ]);
            }
        }

        // Local Videos
        if (request()->hasFile('videos')) {
            foreach (request()->file('videos') as $video) {
                $path = CompressedUploadStorage::storeVideo($video, 'page_sections/videos', 'public');
                $media = $section->media()->create([
                    'type' => 'video',
                    'file_path' => $path,
                ]);
                if ($this->heroCarousel()->isHeroBanner($section, $page)) {
                    $this->heroCarousel()->appendToOrder($section, ['k' => 'video', 'id' => $media->id]);
                }
            }
        }

        // Audios
        if (request()->hasFile('audios')) {
            foreach (request()->file('audios') as $audio) {
                $path = $audio->store('page_sections/audios', 'public');
                $section->media()->create([
                    'type' => 'audio',
                    'file_path' => $path,
                ]);
            }
        }

        $this->addYoutubeLinksFromRequest($section, $page);

        if ($this->heroCarousel()->isHeroBanner($section, $page)) {
            $this->heroCarousel()->syncOrderFromRequest(request(), $section);
        }

        if ($requestedOrder > 0) {
            $this->insertSectionAtPosition($page, $section, $requestedOrder);
        }

        return $this->sectionSaveRedirect($page, 'Section added');
    }

    // Show edit form
    public function editForm(Page $page, PageSection $section)
    {
        if ($this->isHiddenAdminSection($page, $section) && $section->parent_id) {
            return redirect("/console/pages/sections/{$page->id}/edit/{$section->parent_id}")
                ->with('message', 'Manage these links inside the home_marquee section.');
        }

        $section->load(['highlightItems', 'media', 'images', 'subsections.media']);
        $this->syncLegacyYoutubeVideosToMedia($section);
        $section->load('media');
        $page->load('sections');

        return view('pages_console.sections.edit', [
            'page' => $page,
            'section' => $section,
            'highlightItems' => $this->highlightItemsForEditForm($section),
            'marqueeLinks' => $this->marqueeLinksForEditForm($section),
            'carouselSlides' => old('carousel_slides', $this->heroCarousel()->slidesForAdminForm($section)),
        ]);
    }

    // Edit section
    public function edit(Page $page, PageSection $section)
    {
        $this->stripIrrelevantSectionInput($section);

        $attributes = request()->validate(
            array_merge($this->sectionBaseValidationRules(), $this->marqueeValidationRules(), $this->videoUploadValidationRules()),
            $this->videoUploadValidationMessages()
        );

        $this->assertUploadsNotBlockedByPhp(request());

        if ($page->slug === 'home' && ($attributes['section_key'] ?? '') === 'marquee_link') {
            throw ValidationException::withMessages([
                'section_key' => 'Do not use marquee_link here. Edit the home_marquee section and use Add Link.',
            ]);
        }

        $this->assertNotDuplicateHomeGallery($page, $attributes['section_key'] ?? '', $section);

        // Basic fields update
        $section->section_key = $attributes['section_key'];
        $section->title = $attributes['title'] ?? null;
        $section->description = $attributes['description'] ?? null;
        $isHomeMarquee = $page->slug === 'home' && $attributes['section_key'] === 'home_marquee';
        $section->text_color = $isHomeMarquee ? ($attributes['text_color'] ?? null) : null;
        $section->bg_color = $isHomeMarquee ? ($attributes['bg_color'] ?? null) : null;
        $requestedOrder = null;
        if (request()->exists('sort_order') && request()->input('sort_order') !== null && request()->input('sort_order') !== '') {
            $requestedOrder = (int) request()->input('sort_order');
            if ($requestedOrder <= 0) {
                $section->sort_order = 0;
            }
        }
        $section->parent_id = $attributes['parent_id'] ?? null;

        // Replace main image only if new one uploaded
        if (request()->hasFile('image')) {
            if ($section->image) {
                Storage::disk('public')->delete($section->image);
            }
            $section->image = CompressedUploadStorage::storeImage(request()->file('image'), 'page_sections', 'public');
            if ($this->heroCarousel()->isHeroBanner($section, $page)) {
                $this->heroCarousel()->appendToOrder($section, ['k' => 'main_image']);
            }
        }

        $section->save();

        if ($this->isFactsheetHighlightsChild($page, $attributes, $section) && request()->hasFile('videos')) {
            $this->validateHighlightVideoDurations(request()->file('videos'));
        }

        if ($this->isFactsheetHighlightsSection($page, $attributes['section_key'] ?? null)) {
            $this->syncHighlightItems(request(), $section);
        }

        if ($this->isHomeMarqueeSection($page, $attributes['section_key'] ?? null, $section)) {
            $this->syncMarqueeLinks(request(), $page, $section);
        }

        /*
        |--------------------------------------------------------------------------
        | PDFs
        |--------------------------------------------------------------------------
        */

        if (request()->hasFile('pdfs')) {

            // delete only old PDFs
            foreach ($section->media()->where('type', 'pdf')->get() as $media) {
                if ($media->file_path) {
                    Storage::disk('public')->delete($media->file_path);
                }
                $media->delete();
            }

            // add new PDFs
            foreach (request()->file('pdfs') as $pdf) {
                $path = $pdf->store('page_sections/pdfs', 'public');
                $section->media()->create([
                    'type' => 'pdf',
                    'file_path' => $path,
                    'title' => request('new_pdf_title'),
                    'description' => request('new_pdf_description'),
                ]);
            }
        }

        $this->syncPdfMeta($section);

        /*
        |--------------------------------------------------------------------------
        | Videos
        |--------------------------------------------------------------------------
        */

        if (request()->hasFile('videos')) {
            foreach (request()->file('videos') as $video) {
                $path = CompressedUploadStorage::storeVideo($video, 'page_sections/videos', 'public');
                $media = $section->media()->create([
                    'type' => 'video',
                    'file_path' => $path,
                ]);
                if ($this->heroCarousel()->isHeroBanner($section, $page)) {
                    $this->heroCarousel()->appendToOrder($section, ['k' => 'video', 'id' => $media->id]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Audios
        |--------------------------------------------------------------------------
        */

        if (request()->hasFile('audios')) {

            foreach ($section->media()->where('type', 'audio')->get() as $media) {
                if ($media->file_path) {
                    Storage::disk('public')->delete($media->file_path);
                }
                $media->delete();
            }

            foreach (request()->file('audios') as $audio) {
                $path = $audio->store('page_sections/audios', 'public');
                $section->media()->create([
                    'type' => 'audio',
                    'file_path' => $path,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Additional Images (does NOT delete old ones)
        |--------------------------------------------------------------------------
        */

        if (request()->hasFile('images')) {
            foreach (request()->file('images') as $file) {
                $path = CompressedUploadStorage::storeImage($file, 'page_sections/images', 'public');
                $image = $section->images()->create([
                    'image' => $path,
                ]);
                if ($this->heroCarousel()->isHeroBanner($section, $page)) {
                    $this->heroCarousel()->appendToOrder($section, ['k' => 'image', 'id' => $image->id]);
                }
            }
        }

        $this->addYoutubeLinksFromRequest($section, $page);

        if ($this->heroCarousel()->isHeroBanner($section, $page)) {
            $this->heroCarousel()->syncOrderFromRequest(request(), $section);
        }

        if ($requestedOrder !== null && $requestedOrder > 0) {
            $this->insertSectionAtPosition($page, $section, $requestedOrder);
        }

        return $this->sectionSaveRedirect($page, 'Changes saved successfully');
    }

    // Delete section
    public function delete(Page $page, PageSection $section)
    {
        if ($section->image) {
            Storage::disk('public')->delete($section->image);
        }

        foreach ($section->images as $img) {
            Storage::disk('public')->delete($img->image);
            $img->delete();
        }

        foreach ($section->media as $media) {
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();
        }

        foreach ($section->highlightItems as $item) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            if ($item->video_path) {
                Storage::disk('public')->delete($item->video_path);
            }
            $item->delete();
        }

        foreach ($section->subsections as $child) {
            $child->delete();
        }

        $section->delete();

        return redirect("/console/pages/sections/{$page->id}/list")
            ->with('message', 'Section deleted');
    }

    public function move(Page $page, PageSection $section, string $direction)
    {
        if ((int) $section->page_id !== (int) $page->id) {
            abort(404);
        }

        if (! in_array($direction, ['up', 'down'], true)) {
            abort(404);
        }

        $sections = $this->sectionsInDisplayOrder($page)->values();
        $index = $sections->search(fn ($row) => (int) $row->id === (int) $section->id);

        if ($index === false) {
            abort(404);
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapWith < 0 || $swapWith >= $sections->count()) {
            return back()->with('message', 'Section is already at the '.($direction === 'up' ? 'top' : 'bottom'));
        }

        $items = $sections->all();
        [$items[$index], $items[$swapWith]] = [$items[$swapWith], $items[$index]];

        foreach (array_values($items) as $i => $row) {
            $newOrder = $i + 1;
            if ((int) $row->sort_order !== $newOrder) {
                $row->sort_order = $newOrder;
                $row->save();
            }
        }

        return back()->with('message', 'Website order updated');
    }

    public function deleteImage(PageSectionImage $image)
    {
        $section = $image->section;
        $imageId = $image->id;
        Storage::disk('public')->delete($image->image);
        $image->delete();

        if ($section) {
            $this->heroCarousel()->removeFromOrder($section, ['k' => 'image', 'id' => $imageId]);
        }

        return back()->with('message', 'Image deleted');
    }

    public function deleteMedia(PageSectionMedia $media)
    {
        $section = $media->section;
        $mediaId = $media->id;
        $type = $media->type;

        if ($media->file_path) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        if ($section && in_array($type, ['video', 'youtube'], true)) {
            $this->heroCarousel()->removeFromOrder($section, ['k' => $type, 'id' => $mediaId]);
        }

        return back()->with('message', 'File deleted');
    }

    public function deleteMainImage(Page $page, PageSection $section)
    {
        if ($section->page_id !== $page->id) {
            abort(404);
        }

        if ($section->image) {
            Storage::disk('public')->delete($section->image);
            $section->image = null;
            $section->save();
            $this->heroCarousel()->removeFromOrder($section, ['k' => 'main_image']);
        }

        return back()->with('message', 'Main image deleted');
    }

    private function isFactsheetHighlightsChild(Page $page, array $attributes, ?PageSection $section = null): bool
    {
        if ($page->slug !== 'factsheet') {
            return false;
        }

        $parentId = $attributes['parent_id'] ?? $section?->parent_id;
        if (! $parentId) {
            return false;
        }

        $parent = PageSection::find($parentId);
        if (! $parent) {
            return false;
        }

        return (int) $parent->page_id === (int) $page->id
            && $parent->section_key === 'factsheet_highlights';
    }

    private function highlightVideoMaxSeconds(): ?int
    {
        $max = (int) config('upload_compression.highlight_video_max_seconds', 10);

        return $max > 0 ? $max : null;
    }

    private function validateHighlightVideoDurations(array $videos, string $errorKey = 'videos'): void
    {
        $maxSeconds = $this->highlightVideoMaxSeconds();
        if ($maxSeconds === null) {
            return;
        }

        foreach ($videos as $video) {
            if (! $video instanceof UploadedFile) {
                continue;
            }

            $duration = $this->getVideoDurationInSeconds($video);
            // If duration probing is unavailable on this machine, do not block upload.
            if ($duration !== null && $duration > $maxSeconds) {
                throw ValidationException::withMessages([
                    $errorKey => "Highlight videos must be {$maxSeconds} seconds or shorter (yours is ".(int) round($duration).' sec). Use a shorter clip or a YouTube URL.',
                ]);
            }
        }
    }

    private function getVideoDurationInSeconds(UploadedFile $video): ?float
    {
        $ffprobe = $this->resolveFfprobeBinary();
        if (! $ffprobe) {
            return null;
        }

        $videoPath = $video->getRealPath();
        if (! $videoPath) {
            return null;
        }

        $command = sprintf(
            '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>&1',
            escapeshellarg($ffprobe),
            escapeshellarg($videoPath)
        );

        $output = trim((string) shell_exec($command));
        if ($output === '' || ! is_numeric($output)) {
            return null;
        }

        return (float) $output;
    }

    private function resolveFfprobeBinary(): ?string
    {
        static $ffprobePath;

        if ($ffprobePath === false) {
            return null;
        }

        if (is_string($ffprobePath) && $ffprobePath !== '') {
            return $ffprobePath;
        }

        $binary = '';

        if (DIRECTORY_SEPARATOR === '\\') {
            $binary = trim((string) shell_exec('where ffprobe 2>NUL'));
            if ($binary !== '') {
                $lines = preg_split("/\r\n|\n|\r/", $binary);
                $binary = trim((string) ($lines[0] ?? ''));
            }
        } else {
            $binary = trim((string) shell_exec('command -v ffprobe 2>/dev/null'));
        }

        if ($binary === '') {
            $ffprobePath = false;

            return null;
        }

        $ffprobePath = $binary;

        return $ffprobePath;
    }

    private function isFactsheetHighlightsSection(Page $page, ?string $sectionKey): bool
    {
        return $page->slug === 'factsheet' && $sectionKey === 'factsheet_highlights';
    }

    private function isHomeMarqueeSection(Page $page, ?string $sectionKey, ?PageSection $section = null): bool
    {
        if ($page->slug !== 'home' || $sectionKey !== 'home_marquee') {
            return false;
        }

        if ($section && $section->parent_id) {
            return false;
        }

        return true;
    }

    private function isHiddenAdminSection(Page $page, PageSection $section): bool
    {
        if ($page->slug === 'home' && $section->section_key === 'marquee_link' && $section->parent_id) {
            return true;
        }

        return false;
    }

    private function galleryAdminUrl(): string
    {
        $galleryPage = Page::where('slug', 'gallery')->first();

        return $galleryPage
            ? "/console/pages/sections/{$galleryPage->id}"
            : '/console/pages/list';
    }

    private function assertNotDuplicateHomeGallery(Page $page, string $sectionKey, ?PageSection $ignoreSection = null): void
    {
        if ($page->slug !== 'home' || $sectionKey !== 'gallery') {
            return;
        }

        $query = PageSection::where('page_id', $page->id)
            ->where('section_key', 'gallery')
            ->whereNull('parent_id');

        if ($ignoreSection) {
            $query->where('id', '!=', $ignoreSection->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'section_key' => 'Only one gallery section is allowed on the Home page.',
            ]);
        }
    }

    public static function resolveMarqueeLinkHref(PageSection $link): ?string
    {
        $pdf = $link->relationLoaded('media')
            ? $link->media->where('type', 'pdf')->first()
            : $link->media()->where('type', 'pdf')->first();

        if ($pdf?->file_path) {
            return asset('storage/'.$pdf->file_path);
        }

        $url = trim((string) ($link->description ?? ''));

        return $url !== '' ? $url : null;
    }

    private function clearMarqueeLinkFile(PageSection $linkSection): void
    {
        foreach ($linkSection->media()->where('type', 'pdf')->get() as $media) {
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();
        }
    }

    private function syncMarqueeLinks(Request $request, Page $page, PageSection $section): void
    {
        $items = $request->input('marquee_links', []);
        if (! is_array($items)) {
            $items = [];
        }

        $existingItems = $section->subsections()->with('media')->get()->keyBy('id');
        $retainIds = [];

        foreach ($items as $index => $itemData) {
            if (! is_array($itemData)) {
                continue;
            }

            $title = isset($itemData['title']) ? trim((string) $itemData['title']) : '';
            $url = isset($itemData['url']) ? trim((string) $itemData['url']) : '';
            $sortOrder = isset($itemData['sort_order']) && $itemData['sort_order'] !== ''
                ? (int) $itemData['sort_order']
                : ($index + 1);
            $itemId = isset($itemData['id']) ? (int) $itemData['id'] : null;
            $file = $request->file("marquee_links.$index.file");

            /** @var PageSection|null $linkSection */
            $linkSection = $itemId ? $existingItems->get($itemId) : null;
            $existingPdf = $linkSection?->media->where('type', 'pdf')->first();

            if ($title === '' && $url === '' && ! $file && ! $existingPdf) {
                continue;
            }

            if ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL)) {
                throw ValidationException::withMessages([
                    "marquee_links.$index.url" => 'Enter a valid website URL (starting with http:// or https://) or upload a PDF.',
                ]);
            }

            if ($title === '') {
                throw ValidationException::withMessages([
                    "marquee_links.$index.title" => 'Each marquee link needs a label.',
                ]);
            }

            if (! $url && ! $file && ! $existingPdf) {
                throw ValidationException::withMessages([
                    "marquee_links.$index.url" => 'Add a website URL or upload a PDF for each marquee link.',
                ]);
            }

            if (! $linkSection) {
                $linkSection = new PageSection;
                $linkSection->page_id = $page->id;
                $linkSection->section_key = 'marquee_link';
                $linkSection->parent_id = $section->id;
                $linkSection->type = 'single';
            }

            $linkSection->title = $title;
            $linkSection->sort_order = $sortOrder;

            if ($file) {
                if (! $linkSection->exists) {
                    $linkSection->description = $url ?: null;
                    $linkSection->save();
                }

                $this->clearMarqueeLinkFile($linkSection);
                $path = $file->store('page_sections/pdfs', 'public');
                $linkSection->media()->create([
                    'type' => 'pdf',
                    'file_path' => $path,
                ]);
                $linkSection->description = $url ?: null;
            } elseif ($url) {
                $this->clearMarqueeLinkFile($linkSection);
                $linkSection->description = $url;
            } elseif ($existingPdf) {
                $linkSection->description = null;
            }

            $linkSection->save();
            $retainIds[] = $linkSection->id;
        }

        foreach ($existingItems as $id => $linkSection) {
            if (! in_array($id, $retainIds, true)) {
                $this->clearMarqueeLinkFile($linkSection);
                $linkSection->delete();
            }
        }
    }

    private function syncHighlightItems(Request $request, PageSection $section): void
    {
        $items = $request->input('highlight_items', []);
        if (! is_array($items)) {
            $items = [];
        }

        $existingItems = $section->highlightItems()->get()->keyBy('id');
        $retainIds = [];

        foreach ($items as $index => $itemData) {
            if (! is_array($itemData)) {
                continue;
            }

            $title = isset($itemData['title']) ? trim((string) $itemData['title']) : null;
            $description = isset($itemData['description']) ? trim((string) $itemData['description']) : null;
            $youtubeUrl = isset($itemData['youtube_url']) ? trim((string) $itemData['youtube_url']) : null;
            $sortOrder = isset($itemData['sort_order']) && $itemData['sort_order'] !== '' ? (int) $itemData['sort_order'] : 0;
            $itemId = isset($itemData['id']) ? (int) $itemData['id'] : null;

            /** @var PageSectionHighlightItem|null $highlightItem */
            $highlightItem = $itemId ? $existingItems->get($itemId) : null;
            $imageFile = $request->file("highlight_items.$index.image");
            $videoFile = $request->file("highlight_items.$index.video");

            if (
                ! $highlightItem &&
                ! $imageFile &&
                ! $videoFile &&
                ! $youtubeUrl &&
                ! $title &&
                ! $description
            ) {
                continue;
            }

            $hasMediaAfterSave = (bool) $youtubeUrl
                || (bool) $imageFile
                || (bool) $videoFile
                || (bool) ($highlightItem && $highlightItem->image)
                || (bool) ($highlightItem && $highlightItem->video_path);

            if (! $hasMediaAfterSave) {
                throw ValidationException::withMessages([
                    "highlight_items.$index.youtube_url" => 'Each highlight needs at least one: image, video, or YouTube URL.',
                ]);
            }

            if ($videoFile) {
                $this->validateHighlightVideoDurations([$videoFile], "highlight_items.$index.video");
            }

            if (! $highlightItem) {
                $highlightItem = new PageSectionHighlightItem;
                $highlightItem->page_section_id = $section->id;
            }

            $highlightItem->title = $title ?: null;
            $highlightItem->description = $description ?: null;
            $highlightItem->youtube_url = $youtubeUrl ?: null;
            $highlightItem->sort_order = $sortOrder;

            if ($imageFile) {
                if ($highlightItem->image) {
                    Storage::disk('public')->delete($highlightItem->image);
                }
                $highlightItem->image = CompressedUploadStorage::storeImage($imageFile, 'page_sections/highlights/images', 'public');
            }

            if ($videoFile) {
                if ($highlightItem->video_path) {
                    Storage::disk('public')->delete($highlightItem->video_path);
                }
                $highlightItem->video_path = CompressedUploadStorage::storeVideo($videoFile, 'page_sections/highlights/videos', 'public');
            }

            $highlightItem->save();
            $retainIds[] = $highlightItem->id;
        }

        foreach ($existingItems as $existingItem) {
            if (in_array($existingItem->id, $retainIds, true)) {
                continue;
            }

            if ($existingItem->image) {
                Storage::disk('public')->delete($existingItem->image);
            }
            if ($existingItem->video_path) {
                Storage::disk('public')->delete($existingItem->video_path);
            }
            $existingItem->delete();
        }
    }

    private function appendVideoCompressionNotice(string $message): string
    {
        // Keep admin messages silent about compression (runs in background only).
        return $message;
    }

    private function syncPdfMeta(PageSection $section): void
    {
        $meta = request('pdf_meta', []);
        if (! is_array($meta) || $meta === []) {
            return;
        }

        foreach ($meta as $mediaId => $data) {
            $media = $section->media()->where('type', 'pdf')->where('id', $mediaId)->first();
            if (! $media || ! is_array($data)) {
                continue;
            }
            $media->title = isset($data['title']) ? (trim((string) $data['title']) ?: null) : $media->title;
            $media->description = isset($data['description']) ? (trim((string) $data['description']) ?: null) : $media->description;
            $media->save();
        }
    }

    private function youtubeLinksFromRequest(): array
    {
        $links = [];
        foreach ((array) request('youtube_links', []) as $link) {
            $links[] = trim((string) $link);
        }
        foreach (preg_split('/\r\n|\r|\n/', (string) request('youtube_links_text', '')) as $line) {
            $links[] = trim($line);
        }

        $clean = [];
        foreach ($links as $link) {
            if ($link === '') {
                continue;
            }
            if (! preg_match('#^https?://#i', $link)) {
                $link = 'https://'.$link;
            }
            if (! filter_var($link, FILTER_VALIDATE_URL)) {
                continue;
            }
            $clean[] = $link;
        }

        return array_values(array_unique($clean));
    }

    private function addYoutubeLinksFromRequest(PageSection $section, ?Page $page = null): void
    {
        foreach ($this->youtubeLinksFromRequest() as $link) {
            $exists = $section->media()
                ->where('type', 'youtube')
                ->where('youtube_url', $link)
                ->exists();
            if ($exists) {
                continue;
            }
            $media = $section->media()->create([
                'type' => 'youtube',
                'youtube_url' => $link,
            ]);
            if ($this->heroCarousel()->isHeroBanner($section, $page)) {
                $this->heroCarousel()->appendToOrder($section, ['k' => 'youtube', 'id' => $media->id]);
            }
        }
    }

    private function syncLegacyYoutubeVideosToMedia(PageSection $section): void
    {
        $legacy = $section->videos;
        if (! is_array($legacy) || count($legacy) === 0) {
            return;
        }

        $copied = false;
        foreach ($legacy as $url) {
            $url = trim((string) $url);
            if ($url === '' || ! preg_match('/youtu\.?be/i', $url)) {
                continue;
            }
            $exists = $section->media()
                ->where('type', 'youtube')
                ->where('youtube_url', $url)
                ->exists();
            if ($exists) {
                continue;
            }
            $section->media()->create([
                'type' => 'youtube',
                'youtube_url' => $url,
            ]);
            $copied = true;
        }

        if ($copied) {
            $section->forceFill(['videos' => null])->save();
        }
    }

    private function sectionsInDisplayOrder(Page $page)
    {
        return $page->sections()
            ->orderByRaw('CASE WHEN sort_order IS NULL OR sort_order <= 0 THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function insertSectionAtPosition(Page $page, PageSection $section, int $position): void
    {
        $sections = $this->sectionsInDisplayOrder($page)
            ->reject(fn ($row) => (int) $row->id === (int) $section->id)
            ->values();

        $index = max(0, $position - 1);
        if ($index > $sections->count()) {
            $index = $sections->count();
        }

        $section->refresh();
        $sections->splice($index, 0, [$section]);

        foreach ($sections->values() as $i => $row) {
            $newOrder = $i + 1;
            if ((int) $row->sort_order !== $newOrder) {
                $row->sort_order = $newOrder;
                $row->save();
            }
        }
    }

    private function sectionSaveRedirect(Page $page, string $message)
    {
        $message = $this->appendVideoCompressionNotice($message);
        $redirectUrl = "/console/pages/sections/{$page->id}/list";

        if (request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'redirect' => $redirectUrl,
                'message' => $message,
            ]);
        }

        return redirect($redirectUrl)->with('message', $message);
    }

    private function assertUploadsNotBlockedByPhp(Request $request): void
    {
        foreach ($this->flattenUploadedFiles($request->allFiles()) as $file) {
            if (! $file->isValid()) {
                $message = match ($file->getError()) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File "'.$file->getClientOriginalName().'" exceeds the PHP upload limit ('.UploadLimits::effectiveMaxLabel().'). Run: php artisan serve:large',
                    default => 'Upload failed for "'.$file->getClientOriginalName().'".',
                };

                throw ValidationException::withMessages(['videos' => $message]);
            }
        }

        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        $phpLimit = UploadLimits::effectiveMaxBytes();

        if ($contentLength > $phpLimit && $this->countValidUploadedFiles($request) === 0) {
            throw ValidationException::withMessages([
                'videos' => 'The server dropped your upload (PHP limit is '.UploadLimits::effectiveMaxLabel().'). '
                    .'Stop the server, then run: php artisan serve:large  (or .\\serve-large-uploads.bat)',
            ]);
        }
    }

    /** @return list<UploadedFile> */
    private function flattenUploadedFiles(array $files): array
    {
        $flat = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $flat[] = $file;
            } elseif (is_array($file)) {
                $flat = array_merge($flat, $this->flattenUploadedFiles($file));
            }
        }

        return $flat;
    }

    private function countValidUploadedFiles(Request $request): int
    {
        $count = 0;

        foreach ($this->flattenUploadedFiles($request->allFiles()) as $file) {
            if ($file->isValid()) {
                $count++;
            }
        }

        return $count;
    }

    /** @return array<int, array<string, mixed>> */
    private function highlightItemsForEditForm(PageSection $section): array
    {
        $old = old('highlight_items');
        if (is_array($old)) {
            return $old;
        }

        return $section->highlightItems->map(function (PageSectionHighlightItem $item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'sort_order' => $item->sort_order,
                'youtube_url' => $item->youtube_url,
                'existing_image' => $item->image,
                'existing_video_path' => $item->video_path,
            ];
        })->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function marqueeLinksForEditForm(PageSection $section): array
    {
        if ($section->section_key !== 'home_marquee') {
            return [];
        }

        $old = old('marquee_links');
        if (is_array($old) && old('section_key', $section->section_key) === 'home_marquee') {
            return $old;
        }

        return $section->subsections->load('media')->map(function (PageSection $item) {
            $existingPdf = $item->media->where('type', 'pdf')->first();

            return [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->description,
                'sort_order' => $item->sort_order,
                'existing_pdf' => $existingPdf?->file_path,
            ];
        })->all();
    }

    /** @return array<string, string|array<int, string>> */
    private function sectionBaseValidationRules(): array
    {
        $sectionKey = trim((string) request()->input('section_key', ''));

        $rules = [
            'section_key' => 'required',
            'title' => 'nullable',
            'description' => 'nullable',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:51200',
            'images.*' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:51200',
            'pdfs.*' => 'nullable|file|mimes:pdf',
            'pdf_meta' => 'nullable|array',
            'pdf_meta.*.title' => 'nullable|string|max:255',
            'pdf_meta.*.description' => 'nullable|string|max:2000',
            'new_pdf_title' => 'nullable|string|max:255',
            'new_pdf_description' => 'nullable|string|max:2000',
            'audios.*' => 'nullable|file|mimes:mp3,wav,ogg,m4a',
            'youtube_links_text' => 'nullable|string|max:8000',
            'sort_order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:page_sections,id',
        ];

        if ($sectionKey === 'home_marquee') {
            $rules['text_color'] = ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'];
            $rules['bg_color'] = ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'];
        }

        if ($sectionKey === 'factsheet_highlights') {
            $rules = array_merge($rules, [
                'highlight_items' => 'nullable|array',
                'highlight_items.*.id' => 'nullable|integer',
                'highlight_items.*.title' => 'nullable|string|max:255',
                'highlight_items.*.description' => 'nullable|string',
                'highlight_items.*.sort_order' => 'nullable|integer',
                'highlight_items.*.youtube_url' => 'nullable|url',
                'highlight_items.*.image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:51200',
            ]);
        }

        return $rules;
    }

    /** @return array<string, string> */
    private function marqueeValidationRules(): array
    {
        if (trim((string) request()->input('section_key')) !== 'home_marquee') {
            return [];
        }

        return [
            'marquee_links' => 'nullable|array',
            'marquee_links.*.id' => 'nullable|integer',
            'marquee_links.*.title' => 'nullable|string|max:255',
            'marquee_links.*.url' => 'nullable|string|max:2048',
            'marquee_links.*.file' => 'nullable|file|mimes:pdf|max:51200',
            'marquee_links.*.sort_order' => 'nullable|integer',
        ];
    }

    private function stripIrrelevantSectionInput(?PageSection $section = null): void
    {
        $requestedKey = trim((string) request()->input('section_key', $section?->section_key ?? ''));

        if ($requestedKey !== '') {
            request()->merge(['section_key' => $requestedKey]);
        }

        if ($requestedKey !== 'home_marquee') {
            request()->merge([
                'marquee_links' => [],
                'text_color' => null,
                'bg_color' => null,
            ]);
        }

        if ($requestedKey !== 'factsheet_highlights') {
            request()->merge(['highlight_items' => []]);
        }
    }

    private function maxVideoKilobytes(): int
    {
        $configuredMb = (int) config('upload_compression.max_video_mb', 0);
        $phpKb = max(1, (int) floor(UploadLimits::effectiveMaxBytes() / 1024));

        // 0 or negative = no app cap; use PHP/nginx ceiling only.
        if ($configuredMb <= 0) {
            return $phpKb;
        }

        return min($configuredMb * 1024, $phpKb);
    }

    private function maxVideoMegabytes(): int
    {
        return max(1, (int) floor($this->maxVideoKilobytes() / 1024));
    }

    /** @return array<string, string> */
    private function videoUploadValidationRules(): array
    {
        $maxKb = $this->maxVideoKilobytes();
        $rule = "nullable|file|mimes:mp4,mov,avi|max:{$maxKb}";

        return [
            'videos.*' => $rule,
            'highlight_items.*.video' => $rule,
        ];
    }

    /** @return array<string, string> */
    private function videoUploadValidationMessages(): array
    {
        $maxMb = $this->maxVideoMegabytes();

        return [
            'videos.*.max' => "Each video file must be {$maxMb} MB or smaller (server upload limit).",
            'highlight_items.*.video.max' => "Each highlight video must be {$maxMb} MB or smaller (server upload limit).",
        ];
    }

    private function heroCarousel(): HeroCarouselService
    {
        return app(HeroCarouselService::class);
    }
}
