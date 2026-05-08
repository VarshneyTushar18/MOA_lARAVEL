<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Page;
use App\Models\PageSection;

class HomePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure a page with slug 'home' exists
        $page = Page::firstOrCreate(
            ['slug' => 'home'],
            ['title' => 'Home']
        );

        // Sections to ensure exist for the home page.
        // These use the image filenames present in reference/MOA 2/assets/images.
        $sections = [
            // Hero banners (multiple entries with parent_id = null and type banner)
            [
                'section_key' => 'hero_banner',
                'type' => 'banner',
                'title' => null,
                'description' => null,
                'image' => 'pm-yojna-banner.webp',
                'sort_order' => 1,
            ],

            // PM Yojna block
            [
                'section_key' => 'pm_yojna',
                'type' => 'single',
                'title' => 'PM Yojna',
                'description' => 'Programs and initiatives overview',
                'image' => 'pm-yojna.webp',
                'sort_order' => 10,
            ],

            // MoA / ministry block
            [
                'section_key' => 'moa',
                'type' => 'single',
                'title' => 'Ministry of Agriculture',
                'description' => 'About the ministry',
                'image' => 'moa-image.jpg',
                'sort_order' => 20,
            ],

            // AIIA block
            [
                'section_key' => 'aiia',
                'type' => 'single',
                'title' => 'AIIA',
                'description' => 'About AIIA',
                'image' => 'aiia.webp',
                'sort_order' => 30,
            ],

            // RNTCP block
            [
                'section_key' => 'rntcp',
                'type' => 'single',
                'title' => 'RNTCP',
                'description' => 'RNTCP program details',
                'image' => 'ltbi-1.webp',
                'sort_order' => 40,
            ],

            // Roles / team (will be multiple personal entries)
            [
                'section_key' => 'roles',
                'type' => 'personal',
                'title' => 'Director',
                'description' => 'Director description',
                'image' => 'dummy-profile-pic.png',
                'sort_order' => 50,
            ],
        ];

        foreach ($sections as $s) {
            // Try to upsert by page_id + section_key + title to avoid duplicates
            PageSection::updateOrCreate(
                [
                    'page_id' => $page->id,
                    'section_key' => $s['section_key'],
                    'title' => $s['title'],
                ],
                array_merge($s, ['page_id' => $page->id])
            );
        }
    }
}
