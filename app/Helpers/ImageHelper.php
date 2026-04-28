<?php

if (!function_exists('getImageUrl')) {
    /**
     * Get the full URL for an image, whether it's stored in S3 or local storage
     * 
     * @param string|null $imagePath The image path or URL from database
     * @return string|null The full URL to the image
     */
    function getImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        // If it's already a full URL (S3), return as is
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }

        // If it's a local storage path, convert to asset URL
        return asset('storage/' . $imagePath);
    }
}

