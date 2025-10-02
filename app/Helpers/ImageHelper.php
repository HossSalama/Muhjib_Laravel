<?php

if (!function_exists('image_url')) {
    function image_url($path)
    {
        if (!$path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (\Illuminate\Support\Str::endsWith($path, ['.jpg', '.jpeg', '.png', '.webp'])) {
            return asset('storage/' . $path);
        }

        return $path;
    }
}

