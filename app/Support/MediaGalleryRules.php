<?php

namespace App\Support;

class MediaGalleryRules
{
    public const CAROUSEL_THRESHOLD = 4;

    public static function usesCarousel(int $count): bool
    {
        return $count > self::CAROUSEL_THRESHOLD;
    }
}
