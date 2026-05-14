<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image upload compression
    |--------------------------------------------------------------------------
    |
    | When enabled, raster images (JPEG, PNG, WebP) are re-encoded on upload
    | to save disk space. GIF uploads are left as-is to preserve animation.
    | Other file types are never passed through this pipeline.
    |
    */

    'enabled' => env('UPLOAD_IMAGE_COMPRESSION', true),

    'jpeg_quality' => (int) env('UPLOAD_JPEG_QUALITY', 82),

    'png_compression' => (int) env('UPLOAD_PNG_COMPRESSION', 6),

    'webp_quality' => (int) env('UPLOAD_WEBP_QUALITY', 82),

    /*
    |--------------------------------------------------------------------------
    | Max longest edge (pixels)
    |--------------------------------------------------------------------------
    |
    | If width or height exceeds this value, the image is scaled down before
    | encoding. Set to 0 to disable resizing (quality-only compression).
    |
    */

    'max_dimension' => (int) env('UPLOAD_IMAGE_MAX_DIMENSION', 2560),

];
