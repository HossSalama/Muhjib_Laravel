<?php

if (!function_exists('image_url')) {
    function image_url($path, $forPdf = false)
    {
        if (!$path) {
            return null;
        }

        // لو الرابط أصلاً كامل http
        if (preg_match('/^http(s)?:\/\//', $path)) {
            return $path;
        }

        // ✅ DomPDF لازم file://
        if ($forPdf) {
            return 'file://' . public_path('storage/' . ltrim($path, '/'));
        }

        // ✅ باقي الحالات (API, Web)
        return url('storage/' . ltrim($path, '/'));
    }
}
