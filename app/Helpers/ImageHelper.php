<?php

if (!function_exists('image_url')) {
    function image_url($path, $forPdf = false)
    {
        if (!$path) return null;

        // لو الرابط أصلاً كامل http أو Cloudinary
        if (preg_match('/^http(s)?:\/\//', $path)) {
            return $path;
        }

        // لو PDF، رجع المسار المحلي
        if ($forPdf) {
            return 'file://' . public_path('storage/' . ltrim($path, '/'));
        }

        // API أو Web
        return url('storage/' . ltrim($path, '/'));
    }
}

