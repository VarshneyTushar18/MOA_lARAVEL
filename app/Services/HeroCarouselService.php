<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageSection;
use App\Support\Youtube;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HeroCarouselService
{
    private const ORDER_PREFIX = '@carousel:';

    /** @return Collection<int, array<string, mixed>> */
    public function slidesForBanner(PageSection $banner): Collection
    {
        $banner->loadMissing(['images', 'media']);

        $slides = collect();
        foreach ($this->normalizedOrder($banner) as $item) {
            $slide = $this->resolveSlide($banner, $item);
            if ($slide) {
                $slides->push($slide);
            }
        }

        return $slides;
    }

    /** @return array<int, array<string, mixed>> */
    public function slidesForAdminForm(PageSection $banner): array
    {
        $banner->loadMissing(['images', 'media']);
        $order = $this->normalizedOrder($banner);
        $rows = [];

        foreach ($order as $index => $item) {
            $slide = $this->resolveSlide($banner, $item);
            if (! $slide) {
                continue;
            }

            $rows[] = [
                'k' => $item['k'],
                'id' => $item['id'] ?? null,
                'label' => $this->labelForItem($banner, $item, $slide),
                'sort_order' => $index + 1,
            ];
        }

        return $rows;
    }

    public function syncOrderFromRequest(Request $request, PageSection $section): void
    {
        if (! $this->isHeroBanner($section)) {
            return;
        }

        $items = $request->input('carousel_slides', []);
        if (! is_array($items) || $items === []) {
            return;
        }

        usort($items, function ($a, $b) {
            $aOrder = isset($a['sort_order']) ? (int) $a['sort_order'] : 0;
            $bOrder = isset($b['sort_order']) ? (int) $b['sort_order'] : 0;

            return $aOrder <=> $bOrder;
        });

        $order = [];
        foreach ($items as $item) {
            if (! is_array($item) || empty($item['k'])) {
                continue;
            }

            $entry = ['k' => (string) $item['k']];
            if (! empty($item['id'])) {
                $entry['id'] = (int) $item['id'];
            }
            $order[] = $entry;
        }

        $this->writeOrder($section, $this->mergeOrderWithAllMedia($section, $order));
    }

    /** @param array<int, array<string, mixed>> $saved */
    /** @return array<int, array<string, mixed>> */
    private function mergeOrderWithAllMedia(PageSection $section, array $saved): array
    {
        $section->loadMissing(['images', 'media']);
        $merged = [];
        $seen = [];

        foreach ($saved as $item) {
            if ($this->resolveSlide($section, $item) === null) {
                continue;
            }
            $key = $this->itemKey($item);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $merged[] = $item;
        }

        foreach ($this->defaultOrder($section) as $item) {
            $key = $this->itemKey($item);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $merged[] = $item;
        }

        return $this->preferredSort($merged);
    }

    /** @param array<string, mixed> $item */
    public function appendToOrder(PageSection $section, array $item): void
    {
        if (! $this->isHeroBanner($section)) {
            return;
        }

        $order = $this->orderForBanner($section);
        $key = $this->itemKey($item);

        foreach ($order as $existing) {
            if ($this->itemKey($existing) === $key) {
                return;
            }
        }

        $order[] = $item;
        $this->writeOrder($section, $this->preferredSort($order));
    }

    /** @param array<string, mixed> $item */
    public function removeFromOrder(PageSection $section, array $item): void
    {
        if (! $this->isHeroBanner($section)) {
            return;
        }

        $key = $this->itemKey($item);
        $order = array_values(array_filter(
            $this->orderForBanner($section),
            fn ($existing) => $this->itemKey($existing) !== $key
        ));

        $this->writeOrder($section, $order);
    }

    public function isHeroBanner(PageSection $section, ?Page $page = null): bool
    {
        if ($section->section_key !== 'hero_banner') {
            return false;
        }

        if ($page) {
            return $page->slug === 'home';
        }

        $section->loadMissing('page');

        return $section->page?->slug === 'home';
    }

    /** @return array<int, array<string, mixed>> */
    private function normalizedOrder(PageSection $banner): array
    {
        $order = [];
        $seen = [];

        foreach ($this->orderForBanner($banner) as $item) {
            if ($this->resolveSlide($banner, $item) === null) {
                continue;
            }

            $key = $this->itemKey($item);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $order[] = $item;
        }

        foreach ($this->defaultOrder($banner) as $item) {
            $key = $this->itemKey($item);
            if (isset($seen[$key])) {
                continue;
            }

            if ($this->resolveSlide($banner, $item) === null) {
                continue;
            }

            $seen[$key] = true;
            $order[] = $item;
        }

        return $this->preferredSort($order);
    }

    /** @param array<int, array<string, mixed>> $items */
    /** @return array<int, array<string, mixed>> */
    private function preferredSort(array $items): array
    {
        $groups = [
            'main_image' => [],
            'video' => [],
            'youtube' => [],
            'image' => [],
        ];

        foreach ($items as $item) {
            $kind = $item['k'] ?? '';
            if (array_key_exists($kind, $groups)) {
                $groups[$kind][] = $item;
            }
        }

        return array_merge(
            $groups['main_image'],
            $groups['video'],
            $groups['youtube'],
            $groups['image'],
        );
    }

    /** @return array<int, array<string, mixed>> */
    private function orderForBanner(PageSection $banner): array
    {
        $stored = $this->parseStoredOrder($banner);
        if ($stored !== null) {
            return $stored;
        }

        return $this->defaultOrder($banner);
    }

    /** @return array<int, array<string, mixed>>|null */
    private function parseStoredOrder(PageSection $banner): ?array
    {
        $description = (string) ($banner->description ?? '');
        if (! str_starts_with($description, self::ORDER_PREFIX)) {
            return null;
        }

        $json = substr($description, strlen(self::ORDER_PREFIX));
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /** @param array<int, array<string, mixed>> $order */
    private function writeOrder(PageSection $section, array $order): void
    {
        $section->description = self::ORDER_PREFIX.json_encode(array_values($order));
        $section->save();
    }

    /** @return array<int, array<string, mixed>> */
    private function defaultOrder(PageSection $banner): array
    {
        $items = [];

        if ($banner->image) {
            $items[] = ['k' => 'main_image'];
        }

        foreach ($banner->media->where('type', 'video')->sortBy('id') as $video) {
            $items[] = ['k' => 'video', 'id' => $video->id];
        }

        foreach ($banner->media->where('type', 'youtube')->sortBy('id') as $youtube) {
            $items[] = ['k' => 'youtube', 'id' => $youtube->id];
        }

        foreach ($banner->images->sortBy('id') as $image) {
            $items[] = ['k' => 'image', 'id' => $image->id];
        }

        return $items;
    }

    /** @param array<string, mixed> $item */
    private function resolveSlide(PageSection $banner, array $item): ?array
    {
        $kind = $item['k'] ?? null;

        if ($kind === 'main_image' && $banner->image) {
            return [
                'type' => 'image',
                'src' => asset('storage/'.$banner->image),
            ];
        }

        if ($kind === 'image' && ! empty($item['id'])) {
            $image = $banner->images->firstWhere('id', (int) $item['id']);
            if ($image?->image) {
                return [
                    'type' => 'image',
                    'src' => asset('storage/'.$image->image),
                ];
            }
        }

        if ($kind === 'video' && ! empty($item['id'])) {
            $video = $banner->media->firstWhere('id', (int) $item['id']);
            if ($video?->file_path && $video->type === 'video') {
                return [
                    'type' => 'video',
                    'src' => asset('storage/'.$video->file_path),
                    'mime' => $this->videoMimeType($video->file_path),
                ];
            }
        }

        if ($kind === 'youtube' && ! empty($item['id'])) {
            $youtube = $banner->media->firstWhere('id', (int) $item['id']);
            $youtubeId = Youtube::videoId($youtube?->youtube_url);
            if ($youtubeId) {
                return [
                    'type' => 'youtube',
                    'youtube_id' => $youtubeId,
                ];
            }
        }

        return null;
    }

    /** @param array<string, mixed> $item */
    /** @param array<string, mixed> $slide */
    private function labelForItem(PageSection $banner, array $item, array $slide): string
    {
        return match ($item['k'] ?? '') {
            'main_image' => 'Banner image',
            'image' => 'Additional image #'.($item['id'] ?? ''),
            'video' => 'Video: '.basename((string) optional($banner->media->firstWhere('id', (int) ($item['id'] ?? 0)))->file_path),
            'youtube' => 'YouTube: '.(optional($banner->media->firstWhere('id', (int) ($item['id'] ?? 0)))->youtube_url ?? 'Link'),
            default => 'Slide',
        };
    }

    /** @param array<string, mixed> $item */
    private function itemKey(array $item): string
    {
        $kind = $item['k'] ?? 'unknown';
        $id = $item['id'] ?? '';

        return $kind.':'.$id;
    }

    private function videoMimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'webm' => 'video/webm',
            default => 'video/mp4',
        };
    }
}
