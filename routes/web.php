<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Route::get('/fix-storage-link', function () {
//     Artisan::call('storage:link');
//     return '✅ storage link created successfully.';
// });

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';
