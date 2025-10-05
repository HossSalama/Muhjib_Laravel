<?php
function pdf_image_path($url)
{
    if (!$url) return null;

    // لو Cloudinary أو http
    if (preg_match('/^http(s)?:\/\//', $url)) {
        $name = 'cloud_' . md5($url) . '.jpg';
        $path = storage_path('app/public/temp/' . $name);

        if (!file_exists($path)) {
            // تأكد مجلد temp موجود
            if (!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            try {
                file_put_contents($path, file_get_contents($url));
            } catch (\Exception $e) {
                // fallback image لو حصلت مشكلة
                return 'file://' . public_path('images/placeholder.jpg');
            }
        }

        return 'file://' . $path;
    }

    // لو مش http، اعتبره مسار محلي
    return 'file://' . public_path('storage/' . ltrim($url, '/'));
}

