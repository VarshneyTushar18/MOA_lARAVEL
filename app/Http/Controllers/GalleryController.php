<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $page = Page::where('slug', 'gallery')->firstOrFail();

        $albums = $page->sections()
            ->whereNull('parent_id')
            ->with('images')
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (PageSection $section) => self::isAlbumGallerySection($section, $page->slug))
            ->values();

        return view('gallery.index', compact('page', 'albums'));
    }

    public function show(PageSection $section)
    {
        $page = Page::where('slug', 'gallery')->firstOrFail();

        abort_unless((int) $section->page_id === (int) $page->id && $section->parent_id === null, 404);

        $section->load('images');

        $photos = self::collectAlbumPhotos($section);

        abort_if($photos->isEmpty(), 404);

        return view('gallery.album', compact('page', 'section', 'photos'));
    }

    public static function resolveStorageImage(?string $filename): ?string
    {
        if (empty($filename)) {
            return null;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($filename)) {
            return $filename;
        }

        if ($disk->exists('banners/'.$filename)) {
            return 'banners/'.$filename;
        }

        if ($disk->exists('images/'.$filename)) {
            return 'images/'.$filename;
        }

        return null;
    }

    public static function albumCover(PageSection $section): ?string
    {
        $cover = self::resolveStorageImage($section->image);

        if ($cover) {
            return $cover;
        }

        foreach ($section->images as $image) {
            $path = self::resolveStorageImage($image->image);
            if ($path) {
                return $path;
            }
        }

        return null;
    }

    /** @return \Illuminate\Support\Collection<int, string> */
    public static function collectAlbumPhotos(PageSection $section)
    {
        $photos = collect();

        $main = self::resolveStorageImage($section->image);
        if ($main) {
            $photos->push($main);
        }

        foreach ($section->images as $image) {
            $path = self::resolveStorageImage($image->image);
            if ($path && ! $photos->contains($path)) {
                $photos->push($path);
            }
        }

        return $photos->values();
    }

    public static function isSingleGallerySection(PageSection $section, ?string $pageSlug = null): bool
    {
        $key = strtolower(trim($section->section_key ?? ''));

        if (in_array($key, ['gallery_single', 'single', 'singles', 'featured', 'gallery_featured'], true)) {
            return true;
        }

        if ($key !== '' && str_contains($key, 'single')) {
            return true;
        }

        // Gallery page: album_* keys are categories; anything else is a featured single photo.
        if ($pageSlug === 'gallery' && $key !== '') {
            return ! preg_match('/^album([_-]|$)/i', $section->section_key);
        }

        return false;
    }

    public static function isAlbumGallerySection(PageSection $section, ?string $pageSlug = null): bool
    {
        if ($pageSlug !== 'gallery') {
            return true;
        }

        return ! self::isSingleGallerySection($section, $pageSlug);
    }

    /**
     * Unified home + /gallery data: singles (top) and album categories (below).
     *
     * @return array{heading: string, preview: ?string, singles: \Illuminate\Support\Collection, albums: \Illuminate\Support\Collection, show: bool}
     */
    public static function homeTeaserData(?Page $homePage = null): array
    {
        $galleryPage = Page::where('slug', 'gallery')
            ->with(['sections' => fn ($q) => $q->whereNull('parent_id')->with('images')->orderBy('sort_order')])
            ->first();

        $singles = collect();
        $albums = collect();

        if ($galleryPage) {
            foreach ($galleryPage->sections as $section) {
                $photos = self::collectAlbumPhotos($section);

                if (self::isSingleGallerySection($section, $galleryPage->slug)) {
                    foreach ($photos as $photo) {
                        $singles->push([
                            'path' => $photo,
                            'title' => $section->title,
                            'url' => route('gallery.index'),
                        ]);
                    }

                    continue;
                }

                if (! self::isAlbumGallerySection($section, $galleryPage->slug)) {
                    continue;
                }

                $cover = self::albumCover($section);
                if (! $cover) {
                    continue;
                }

                $albums->push([
                    'id' => $section->id,
                    'title' => $section->title ?: 'Untitled Album',
                    'cover' => $cover,
                    'url' => route('gallery.show', $section),
                    'count' => $photos->count(),
                ]);
            }
        }

        if ($homePage) {
            $legacy = $homePage->sections->firstWhere('section_key', 'gallery');
            if ($legacy && ! $legacy->parent_id) {
                if (! $legacy->relationLoaded('images')) {
                    $legacy->load('images');
                }

                foreach (self::collectAlbumPhotos($legacy) as $photo) {
                    $singles->push([
                        'path' => $photo,
                        'title' => $legacy->title,
                        'url' => route('gallery.index'),
                    ]);
                }
            }
        }

        $singles = $singles
            ->unique(fn (array $item) => $item['path'])
            ->values();

        $preview = $singles->first()['path'] ?? ($albums->first()['cover'] ?? null);

        return [
            'heading' => $galleryPage->title ?? 'Photo Gallery',
            'preview' => $preview,
            'singles' => $singles,
            'albums' => $albums,
            'show' => $singles->isNotEmpty() || $albums->isNotEmpty(),
        ];
    }
}
