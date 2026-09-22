<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageSection;
use App\Services\CompressedUploadStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FooterContentService
{
    public const PAGE_SLUG = 'site-footer';

    public function ensurePage(): Page
    {
        $page = Page::firstOrCreate(
            ['slug' => self::PAGE_SLUG],
            ['title' => 'Site Footer']
        );

        if ($page->sections()->count() === 0) {
            $this->seedDefaults($page);
        }

        return $page;
    }

    public function forFrontend(): array
    {
        $page = Page::where('slug', self::PAGE_SLUG)
            ->with([
                'sections.subsections',
                'sections.images',
            ])
            ->first();

        if (! $page) {
            return $this->defaultFrontendData();
        }

        $sections = $page->sections->keyBy('section_key');

        $branding = $sections->get('footer_branding');
        $useful = $sections->get('footer_useful_links');
        $important = $sections->get('footer_important_links');
        $contact = $sections->get('footer_contact');
        $bottom = $sections->get('footer_bottom');

        $defaults = $this->defaultFrontendData();

        return [
            'logo' => [
                'url' => $branding?->image
                    ? asset('storage/'.$branding->image)
                    : $defaults['logo']['url'],
                'alt' => filled($branding?->title) ? $branding->title : $defaults['logo']['alt'],
            ],
            'sponsors' => $this->sponsorsForFrontend($branding, $defaults['sponsors']),
            'useful_links' => [
                'title' => filled($useful?->title) ? $useful->title : $defaults['useful_links']['title'],
                'items' => $this->linksForFrontend($useful, $defaults['useful_links']['items']),
            ],
            'important_links' => [
                'title' => filled($important?->title) ? $important->title : $defaults['important_links']['title'],
                'items' => $this->linksForFrontend($important, $defaults['important_links']['items']),
            ],
            'contact' => [
                'title' => filled($contact?->title) ? $contact->title : $defaults['contact']['title'],
                'address' => filled($contact?->description) ? $contact->description : $defaults['contact']['address'],
                'phone' => $this->contactLine($contact, 'footer_contact_phone', $defaults['contact']['phone']),
                'email' => $this->contactLine($contact, 'footer_contact_email', $defaults['contact']['email']),
            ],
            'bottom' => [
                'copyright' => filled($bottom?->description) ? $bottom->description : $defaults['bottom']['copyright'],
                'legal_links' => $this->linksForFrontend($bottom, $defaults['bottom']['legal_links']),
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function forAdminForm(): array
    {
        $page = $this->ensurePage()->load([
            'sections.subsections',
            'sections.images',
        ]);

        $sections = $page->sections->keyBy('section_key');
        $branding = $sections->get('footer_branding') ?? $this->makeSection($page, 'footer_branding');
        $useful = $sections->get('footer_useful_links') ?? $this->makeSection($page, 'footer_useful_links');
        $important = $sections->get('footer_important_links') ?? $this->makeSection($page, 'footer_important_links');
        $contact = $sections->get('footer_contact') ?? $this->makeSection($page, 'footer_contact');
        $bottom = $sections->get('footer_bottom') ?? $this->makeSection($page, 'footer_bottom');

        $phone = $contact->subsections->firstWhere('section_key', 'footer_contact_phone');
        $email = $contact->subsections->firstWhere('section_key', 'footer_contact_email');

        return [
            'page' => $page,
            'logo_alt' => old('logo_alt', $branding->title),
            'existing_logo' => $branding->image,
            'sponsors' => old('sponsors', $this->sponsorsForAdmin($branding)),
            'useful_links_title' => old('useful_links_title', $useful->title),
            'useful_links' => old('useful_links', $this->linksForAdmin($useful)),
            'important_links_title' => old('important_links_title', $important->title),
            'important_links' => old('important_links', $this->linksForAdmin($important)),
            'contact_title' => old('contact_title', $contact->title),
            'address' => old('address', $contact->description),
            'phone' => old('phone', $phone?->description),
            'email' => old('email', $email?->description),
            'copyright' => old('copyright', $bottom->description),
            'legal_links' => old('legal_links', $this->linksForAdmin($bottom)),
        ];
    }

    public function saveFromRequest(Request $request): void
    {
        $request->validate([
            'logo' => 'nullable|image',
            'logo_alt' => 'nullable|string|max:255',
            'sponsors' => 'nullable|array',
            'sponsors.*.id' => 'nullable|integer',
            'sponsors.*.alt' => 'nullable|string|max:255',
            'sponsors.*.image' => 'nullable|image',
            'sponsors.*.sort_order' => 'nullable|integer',
            'useful_links_title' => 'nullable|string|max:255',
            'useful_links' => 'nullable|array',
            'useful_links.*.id' => 'nullable|integer',
            'useful_links.*.title' => 'nullable|string|max:255',
            'useful_links.*.url' => 'nullable|string|max:2048',
            'useful_links.*.sort_order' => 'nullable|integer',
            'important_links_title' => 'nullable|string|max:255',
            'important_links' => 'nullable|array',
            'important_links.*.id' => 'nullable|integer',
            'important_links.*.title' => 'nullable|string|max:255',
            'important_links.*.url' => 'nullable|string|max:2048',
            'important_links.*.sort_order' => 'nullable|integer',
            'contact_title' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'copyright' => 'nullable|string|max:500',
            'legal_links' => 'nullable|array',
            'legal_links.*.id' => 'nullable|integer',
            'legal_links.*.title' => 'nullable|string|max:255',
            'legal_links.*.url' => 'nullable|string|max:2048',
            'legal_links.*.sort_order' => 'nullable|integer',
        ]);

        $page = $this->ensurePage();
        $sections = $page->sections()->with('subsections')->get()->keyBy('section_key');

        $branding = $sections->get('footer_branding') ?? $this->makeSection($page, 'footer_branding');
        $branding->title = $request->input('logo_alt');
        if ($request->hasFile('logo')) {
            if ($branding->image) {
                Storage::disk('public')->delete($branding->image);
            }
            $branding->image = CompressedUploadStorage::storeImage($request->file('logo'), 'page_sections', 'public');
        }
        $branding->save();

        $this->syncSponsors($request, $page, $branding);

        $useful = $sections->get('footer_useful_links') ?? $this->makeSection($page, 'footer_useful_links');
        $useful->title = $request->input('useful_links_title');
        $useful->save();
        $this->syncLinks($request, $page, $useful, 'useful_links');

        $important = $sections->get('footer_important_links') ?? $this->makeSection($page, 'footer_important_links');
        $important->title = $request->input('important_links_title');
        $important->save();
        $this->syncLinks($request, $page, $important, 'important_links');

        $contact = $sections->get('footer_contact') ?? $this->makeSection($page, 'footer_contact');
        $contact->title = $request->input('contact_title');
        $contact->description = $request->input('address');
        $contact->save();
        $this->syncContactLine($contact, 'footer_contact_phone', $request->input('phone'));
        $this->syncContactLine($contact, 'footer_contact_email', $request->input('email'));

        $bottom = $sections->get('footer_bottom') ?? $this->makeSection($page, 'footer_bottom');
        $bottom->description = $request->input('copyright');
        $bottom->save();
        $this->syncLinks($request, $page, $bottom, 'legal_links');
    }

    /** @return array<string, mixed> */
    private function defaultFrontendData(): array
    {
        return [
            'logo' => [
                'url' => asset('assets/images/Main-logo.png?v=20260921moalogo'),
                'alt' => 'Ministry of Ayush',
            ],
            'sponsors' => [
                ['url' => asset('assets/images/aiia.webp'), 'alt' => ''],
                ['url' => asset('assets/images/pm-yojna.webp'), 'alt' => ''],
                ['url' => asset('assets/images/nam.webp'), 'alt' => 'Ministry of Ayush'],
            ],
            'useful_links' => [
                'title' => 'Useful Links',
                'items' => [
                    ['label' => 'About us', 'url' => '/about'],
                    ['label' => 'Fact Sheet', 'url' => '/factsheet'],
                    ['label' => 'ACSM / IEC', 'url' => '/acsm_iec'],
                    ['label' => 'Performance report', 'url' => '/performance_report'],
                    ['label' => 'Best Practices', 'url' => '/best_practices'],
                    ['label' => 'Patient Corner', 'url' => '/patient_corner'],
                ],
            ],
            'important_links' => [
                'title' => 'Important Links',
                'items' => [
                    ['label' => 'National Ayush Mission (NAM)', 'url' => '#'],
                    ['label' => 'Central Sector Schemes', 'url' => '#'],
                    ['label' => 'Public Grievances', 'url' => '#'],
                    ['label' => "Explore What's new", 'url' => '#'],
                    ['label' => 'Explore Press Release', 'url' => '#'],
                    ['label' => 'Explore Vacancy', 'url' => '#'],
                ],
            ],
            'contact' => [
                'title' => 'Contact Information',
                'address' => "All India Institute of Ayurveda (AIIA) Mathura Road, Gautam Puri\nSarita Vihar, Delhi - 110076",
                'phone' => 'Phone No : 011-26950401/402',
                'email' => 'Email Id : contact-us@aiia.gov.in',
            ],
            'bottom' => [
                'copyright' => '© Copyright 2026 Ministry of Ayush. All Rights Reserved',
                'legal_links' => [
                    ['label' => 'Terms & Conditions', 'url' => '#'],
                    ['label' => 'Privacy Policy', 'url' => '#'],
                ],
            ],
        ];
    }

    private function seedDefaults(Page $page): void
    {
        $defaults = $this->defaultFrontendData();

        $branding = $this->makeSection($page, 'footer_branding');
        $branding->title = $defaults['logo']['alt'];
        $branding->save();

        $useful = $this->makeSection($page, 'footer_useful_links');
        $useful->title = $defaults['useful_links']['title'];
        $useful->save();
        $this->seedLinks($page, $useful, $defaults['useful_links']['items']);

        $important = $this->makeSection($page, 'footer_important_links');
        $important->title = $defaults['important_links']['title'];
        $important->save();
        $this->seedLinks($page, $important, $defaults['important_links']['items']);

        $contact = $this->makeSection($page, 'footer_contact');
        $contact->title = $defaults['contact']['title'];
        $contact->description = $defaults['contact']['address'];
        $contact->save();
        $this->syncContactLine($contact, 'footer_contact_phone', $defaults['contact']['phone']);
        $this->syncContactLine($contact, 'footer_contact_email', $defaults['contact']['email']);

        $bottom = $this->makeSection($page, 'footer_bottom');
        $bottom->description = $defaults['bottom']['copyright'];
        $bottom->save();
        $this->seedLinks($page, $bottom, $defaults['bottom']['legal_links']);
    }

    private function makeSection(Page $page, string $sectionKey): PageSection
    {
        $section = new PageSection;
        $section->page_id = $page->id;
        $section->section_key = $sectionKey;
        $section->type = 'single';
        $section->sort_order = 0;
        $section->save();

        return $section;
    }

    /** @param array<int, array{label: string, url: string}> $items */
    private function seedLinks(Page $page, PageSection $parent, array $items): void
    {
        foreach ($items as $index => $item) {
            $link = new PageSection;
            $link->page_id = $page->id;
            $link->section_key = 'footer_link';
            $link->parent_id = $parent->id;
            $link->type = 'single';
            $link->title = $item['label'];
            $link->description = $item['url'];
            $link->sort_order = $index + 1;
            $link->save();
        }
    }

    /** @param array<int, array{url: string, alt: string}> $fallback */
    private function sponsorsForFrontend(?PageSection $branding, array $fallback): array
    {
        if (! $branding) {
            return $fallback;
        }

        $sponsors = $branding->subsections
            ->where('section_key', 'footer_sponsor')
            ->filter(fn (PageSection $item) => filled($item->image))
            ->sortBy('sort_order')
            ->values()
            ->map(fn (PageSection $item) => [
                'url' => asset('storage/'.$item->image),
                'alt' => $item->title ?? '',
            ])
            ->all();

        return $sponsors !== [] ? $sponsors : $fallback;
    }

    /** @param array<int, array{label: string, url: string}> $fallback */
    private function linksForFrontend(?PageSection $parent, array $fallback): array
    {
        if (! $parent) {
            return $fallback;
        }

        $items = $parent->subsections
            ->where('section_key', 'footer_link')
            ->filter(fn (PageSection $item) => filled($item->title))
            ->sortBy('sort_order')
            ->values()
            ->map(fn (PageSection $item) => [
                'label' => $item->title,
                'url' => filled($item->description) ? $item->description : '#',
            ])
            ->all();

        return $items !== [] ? $items : $fallback;
    }

    private function contactLine(?PageSection $contact, string $key, string $fallback): string
    {
        if (! $contact) {
            return $fallback;
        }

        $line = $contact->subsections->firstWhere('section_key', $key);

        return filled($line?->description) ? $line->description : $fallback;
    }

    /** @return array<int, array<string, mixed>> */
    private function sponsorsForAdmin(PageSection $branding): array
    {
        return $branding->subsections
            ->where('section_key', 'footer_sponsor')
            ->sortBy('sort_order')
            ->values()
            ->map(fn (PageSection $item) => [
                'id' => $item->id,
                'alt' => $item->title,
                'existing_image' => $item->image,
                'sort_order' => $item->sort_order,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function linksForAdmin(PageSection $parent): array
    {
        return $parent->subsections
            ->where('section_key', 'footer_link')
            ->sortBy('sort_order')
            ->values()
            ->map(fn (PageSection $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->description,
                'sort_order' => $item->sort_order,
            ])
            ->all();
    }

    private function syncSponsors(Request $request, Page $page, PageSection $branding): void
    {
        $items = $request->input('sponsors', []);
        if (! is_array($items)) {
            $items = [];
        }

        $existing = $branding->subsections()->where('section_key', 'footer_sponsor')->get()->keyBy('id');
        $retainIds = [];

        foreach ($items as $index => $itemData) {
            if (! is_array($itemData)) {
                continue;
            }

            $alt = isset($itemData['alt']) ? trim((string) $itemData['alt']) : '';
            $sortOrder = isset($itemData['sort_order']) && $itemData['sort_order'] !== ''
                ? (int) $itemData['sort_order']
                : ($index + 1);
            $itemId = isset($itemData['id']) ? (int) $itemData['id'] : null;
            $file = $request->file("sponsors.$index.image");

            /** @var PageSection|null $sponsor */
            $sponsor = $itemId ? $existing->get($itemId) : null;

            if (! $file && ! $sponsor?->image && $alt === '') {
                continue;
            }

            if (! $sponsor) {
                $sponsor = new PageSection;
                $sponsor->page_id = $page->id;
                $sponsor->section_key = 'footer_sponsor';
                $sponsor->parent_id = $branding->id;
                $sponsor->type = 'single';
            }

            $sponsor->title = $alt !== '' ? $alt : null;
            $sponsor->sort_order = $sortOrder;

            if ($file) {
                if ($sponsor->image) {
                    Storage::disk('public')->delete($sponsor->image);
                }
                $sponsor->image = CompressedUploadStorage::storeImage($file, 'page_sections', 'public');
            }

            if (! $sponsor->image) {
                throw ValidationException::withMessages([
                    "sponsors.$index.image" => 'Each sponsor logo needs an uploaded image.',
                ]);
            }

            $sponsor->save();
            $retainIds[] = $sponsor->id;
        }

        foreach ($existing as $id => $sponsor) {
            if (! in_array($id, $retainIds, true)) {
                if ($sponsor->image) {
                    Storage::disk('public')->delete($sponsor->image);
                }
                $sponsor->delete();
            }
        }
    }

    private function syncLinks(Request $request, Page $page, PageSection $parent, string $inputKey): void
    {
        $items = $request->input($inputKey, []);
        if (! is_array($items)) {
            $items = [];
        }

        $existing = $parent->subsections()->where('section_key', 'footer_link')->get()->keyBy('id');
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

            if ($title === '' && $url === '') {
                continue;
            }

            if ($title === '') {
                throw ValidationException::withMessages([
                    "$inputKey.$index.title" => 'Each link needs a label.',
                ]);
            }

            /** @var PageSection|null $link */
            $link = $itemId ? $existing->get($itemId) : null;

            if (! $link) {
                $link = new PageSection;
                $link->page_id = $page->id;
                $link->section_key = 'footer_link';
                $link->parent_id = $parent->id;
                $link->type = 'single';
            }

            $link->title = $title;
            $link->description = $url !== '' ? $url : '#';
            $link->sort_order = $sortOrder;
            $link->save();
            $retainIds[] = $link->id;
        }

        foreach ($existing as $id => $link) {
            if (! in_array($id, $retainIds, true)) {
                $link->delete();
            }
        }
    }

    private function syncContactLine(PageSection $contact, string $sectionKey, ?string $value): void
    {
        $value = trim((string) $value);
        $line = $contact->subsections()->where('section_key', $sectionKey)->first();

        if ($value === '') {
            $line?->delete();

            return;
        }

        if (! $line) {
            $line = new PageSection;
            $line->page_id = $contact->page_id;
            $line->section_key = $sectionKey;
            $line->parent_id = $contact->id;
            $line->type = 'single';
            $line->sort_order = $sectionKey === 'footer_contact_phone' ? 1 : 2;
        }

        $line->description = $value;
        $line->save();
    }
}
