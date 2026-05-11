<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionImage;
use App\Models\PageSectionMedia;
use App\Models\PageSectionHighlightItem;

class PageSectionsController extends Controller
{
    // List sections
    public function list(Page $page)
    {
        return view('pages_console.sections.list', [
            'page' => $page,
            'sections' => $page->sections()->with(['images','media','parent'])->orderBy('sort_order')->get(),
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
        $attributes = request()->validate([
            'section_key' => 'required',
            'title' => 'nullable',
            'description' => 'nullable',
            'text_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'bg_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'image' => 'nullable|image',
            'images.*' => 'nullable|image',
            'pdfs.*' => 'nullable|file|mimes:pdf',
            'videos.*' => 'nullable|file|mimes:mp4,mov,avi',
            'audios.*' => 'nullable|file|mimes:mp3,wav,ogg,m4a',
            'youtube_links.*' => 'nullable|url',
            'highlight_items' => 'nullable|array',
            'highlight_items.*.title' => 'nullable|string|max:255',
            'highlight_items.*.description' => 'nullable|string',
            'highlight_items.*.sort_order' => 'nullable|integer',
            'highlight_items.*.youtube_url' => 'nullable|url',
            'highlight_items.*.image' => 'nullable|image',
            'highlight_items.*.video' => 'nullable|file|mimes:mp4,mov,avi',
            'sort_order' => 'nullable|integer',
            'parent_id' => 'nullable|exists:page_sections,id',
        ]);

        $section = new PageSection();
        $section->page_id = $page->id;
        $section->section_key = $attributes['section_key'];
        $section->title = $attributes['title'] ?? null;
        $section->description = $attributes['description'] ?? null;
        $isHomeMarquee = $page->slug === 'home' && $attributes['section_key'] === 'home_marquee';
        $section->text_color = $isHomeMarquee ? ($attributes['text_color'] ?? null) : null;
        $section->bg_color = $isHomeMarquee ? ($attributes['bg_color'] ?? null) : null;
        $section->sort_order = $attributes['sort_order'] ?? 0;
        $section->parent_id = $attributes['parent_id'] ?? null;

        if (request()->hasFile('image')) {
            $section->image = request()->file('image')->store('page_sections', 'public');
        }

        $section->save();

        if ($this->isFactsheetHighlightsChild($page, $attributes) && request()->hasFile('videos')) {
            $this->validateHighlightVideoDurations(request()->file('videos'));
        }

        if ($this->isFactsheetHighlightsSection($page, $attributes['section_key'] ?? null)) {
            $this->syncHighlightItems(request(), $section);
        }

        // Multiple images
        if (request()->hasFile('images')) {
            foreach (request()->file('images') as $file) {
                $path = $file->store('page_sections/images', 'public');
                $section->images()->create(['image' => $path]);
            }
        }

        // PDFs
        if (request()->hasFile('pdfs')) {
            foreach (request()->file('pdfs') as $pdf) {
                $path = $pdf->store('page_sections/pdfs', 'public');
                $section->media()->create([
                    'type' => 'pdf',
                    'file_path' => $path
                ]);
            }
        }

        // Local Videos
        if (request()->hasFile('videos')) {
            foreach (request()->file('videos') as $video) {
                $path = $video->store('page_sections/videos', 'public');
                $section->media()->create([
                    'type' => 'video',
                    'file_path' => $path
                ]);
            }
        }

        // Audios
        if (request()->hasFile('audios')) {
            foreach (request()->file('audios') as $audio) {
                $path = $audio->store('page_sections/audios', 'public');
                $section->media()->create([
                    'type' => 'audio',
                    'file_path' => $path
                ]);
            }
        }

        // YouTube
        if (request('youtube_links')) {
            foreach (request('youtube_links') as $link) {
                if ($link) {
                    $section->media()->create([
                        'type' => 'youtube',
                        'youtube_url' => $link
                    ]);
                }
            }
        }

        return redirect("/console/pages/sections/{$page->id}/list")
            ->with('message', 'Section added');
    }

    // Show edit form
    public function editForm(Page $page, PageSection $section)
    {
        $section->load('highlightItems');
        return view('pages_console.sections.edit', [
            'page' => $page,
            'section' => $section,
        ]);
    }

    // Edit section
    public function edit(Page $page, PageSection $section)
{
    $attributes = request()->validate([
        'section_key' => 'required',
        'title' => 'nullable',
        'description' => 'nullable',
        'text_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        'bg_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        'image' => 'nullable|image',
        'images.*' => 'nullable|image',
        'pdfs.*' => 'nullable|file|mimes:pdf',
        'videos.*' => 'nullable|file|mimes:mp4,mov,avi',
        'audios.*' => 'nullable|file|mimes:mp3,wav,ogg,m4a',
        'youtube_links.*' => 'nullable|url',
        'highlight_items' => 'nullable|array',
        'highlight_items.*.id' => 'nullable|integer',
        'highlight_items.*.title' => 'nullable|string|max:255',
        'highlight_items.*.description' => 'nullable|string',
        'highlight_items.*.sort_order' => 'nullable|integer',
        'highlight_items.*.youtube_url' => 'nullable|url',
        'highlight_items.*.image' => 'nullable|image',
        'highlight_items.*.video' => 'nullable|file|mimes:mp4,mov,avi',
        'sort_order' => 'nullable|integer',
        'parent_id' => 'nullable|exists:page_sections,id',
    ]);

    // Basic fields update
    $section->section_key = $attributes['section_key'];
    $section->title = $attributes['title'] ?? null;
    $section->description = $attributes['description'] ?? null;
    $isHomeMarquee = $page->slug === 'home' && $attributes['section_key'] === 'home_marquee';
    $section->text_color = $isHomeMarquee ? ($attributes['text_color'] ?? null) : null;
    $section->bg_color = $isHomeMarquee ? ($attributes['bg_color'] ?? null) : null;
    $section->sort_order = $attributes['sort_order'] ?? 0;
    $section->parent_id = $attributes['parent_id'] ?? null;

    // Replace main image only if new one uploaded
    if (request()->hasFile('image')) {
        if ($section->image) {
            Storage::disk('public')->delete($section->image);
        }
        $section->image = request()->file('image')->store('page_sections', 'public');
    }

    $section->save();

    if ($this->isFactsheetHighlightsChild($page, $attributes, $section) && request()->hasFile('videos')) {
        $this->validateHighlightVideoDurations(request()->file('videos'));
    }

    if ($this->isFactsheetHighlightsSection($page, $attributes['section_key'] ?? null)) {
        $this->syncHighlightItems(request(), $section);
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
                'file_path' => $path
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Videos
    |--------------------------------------------------------------------------
    */

    if (request()->hasFile('videos')) {

        foreach ($section->media()->where('type', 'video')->get() as $media) {
            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();
        }

        foreach (request()->file('videos') as $video) {
            $path = $video->store('page_sections/videos', 'public');
            $section->media()->create([
                'type' => 'video',
                'file_path' => $path
            ]);
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
                'file_path' => $path
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | YouTube Links
    |--------------------------------------------------------------------------
    */

    if (request('youtube_links')) {

        // delete old youtube links
        foreach ($section->media()->where('type', 'youtube')->get() as $media) {
            $media->delete();
        }

        foreach (request('youtube_links') as $link) {
            if ($link) {
                $section->media()->create([
                    'type' => 'youtube',
                    'youtube_url' => $link
                ]);
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Additional Images (does NOT delete old ones)
    |--------------------------------------------------------------------------
    */

    if (request()->hasFile('images')) {
        foreach (request()->file('images') as $file) {
            $path = $file->store('page_sections/images', 'public');
            $section->images()->create([
                'image' => $path
            ]);
        }
    }

    return redirect("/console/pages/sections/{$page->id}/list")
        ->with('message', 'Changes saved successfully');
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

        $section->delete();

        return redirect("/console/pages/sections/{$page->id}/list")
            ->with('message', 'Section deleted');
    }

    public function deleteImage(PageSectionImage $image)
    {
        Storage::disk('public')->delete($image->image);
        $image->delete();
        return back()->with('message', 'Image deleted');
    }

    private function isFactsheetHighlightsChild(Page $page, array $attributes, ?PageSection $section = null): bool
    {
        if ($page->slug !== 'factsheet') {
            return false;
        }

        $parentId = $attributes['parent_id'] ?? $section?->parent_id;
        if (!$parentId) {
            return false;
        }

        $parent = PageSection::find($parentId);
        if (!$parent) {
            return false;
        }

        return (int) $parent->page_id === (int) $page->id
            && $parent->section_key === 'factsheet_highlights';
    }

    private function validateHighlightVideoDurations(array $videos): void
    {
        foreach ($videos as $video) {
            if (!$video instanceof UploadedFile) {
                continue;
            }

            $duration = $this->getVideoDurationInSeconds($video);
            // If duration probing is unavailable on this machine, do not block upload.
            // We still enforce the max-duration rule whenever probing succeeds.
            if ($duration !== null && $duration > 10) {
                throw ValidationException::withMessages([
                    'videos' => 'Highlight videos must be 10 seconds or shorter.',
                ]);
            }
        }
    }

    private function getVideoDurationInSeconds(UploadedFile $video): ?float
    {
        $ffprobe = $this->resolveFfprobeBinary();
        if (!$ffprobe) {
            return null;
        }

        $videoPath = $video->getRealPath();
        if (!$videoPath) {
            return null;
        }

        $command = sprintf(
            '%s -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 %s 2>&1',
            escapeshellarg($ffprobe),
            escapeshellarg($videoPath)
        );

        $output = trim((string) shell_exec($command));
        if ($output === '' || !is_numeric($output)) {
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

    private function syncHighlightItems(Request $request, PageSection $section): void
    {
        $items = $request->input('highlight_items', []);
        if (!is_array($items)) {
            $items = [];
        }

        $existingItems = $section->highlightItems()->get()->keyBy('id');
        $retainIds = [];

        foreach ($items as $index => $itemData) {
            if (!is_array($itemData)) {
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
                !$highlightItem &&
                !$imageFile &&
                !$videoFile &&
                !$youtubeUrl &&
                !$title &&
                !$description
            ) {
                continue;
            }

            $hasMediaAfterSave = (bool) $youtubeUrl
                || (bool) $imageFile
                || (bool) $videoFile
                || (bool) ($highlightItem && $highlightItem->image)
                || (bool) ($highlightItem && $highlightItem->video_path);

            if (!$hasMediaAfterSave) {
                throw ValidationException::withMessages([
                    "highlight_items.$index.youtube_url" => 'Each highlight needs at least one: image, video, or YouTube URL.',
                ]);
            }

            if ($videoFile) {
                $this->validateHighlightVideoDurations([$videoFile]);
            }

            if (!$highlightItem) {
                $highlightItem = new PageSectionHighlightItem();
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
                $highlightItem->image = $imageFile->store('page_sections/highlights/images', 'public');
            }

            if ($videoFile) {
                if ($highlightItem->video_path) {
                    Storage::disk('public')->delete($highlightItem->video_path);
                }
                $highlightItem->video_path = $videoFile->store('page_sections/highlights/videos', 'public');
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
}