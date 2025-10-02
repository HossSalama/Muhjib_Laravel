<?php

function image_url($path)
{
    if (!$path) {
        return null;
    }

    // لو الرابط كامل (http) رجعه زي ما هو
    if (preg_match('/^http(s)?:\/\//', $path)) {
        return $path;
    }

    // خلي المسار مطلق عشان dompdf يقرأه
    return public_path('storage/' . ltrim($path, '/'));
}


