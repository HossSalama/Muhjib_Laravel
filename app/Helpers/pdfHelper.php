<?php

if (!function_exists('file_url')) {
    function file_url(?string $path): ?string
    {
        return $path ? url('storage/' . ltrim($path, '/')) : null;
    }
}