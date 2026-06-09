<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user-app/{any?}', function ($any = '') {
    $path = public_path('user-app/index.html');
    return file_exists($path)
        ? response(file_get_contents($path))->header('Content-Type', 'text/html')
        : response('Not found', 404);
})->where('any', '.*');

Route::get('/admin/{any?}', function ($any = '') {
    $path = public_path('admin/index.html');
    return file_exists($path)
        ? response(file_get_contents($path))->header('Content-Type', 'text/html')
        : response('Not found', 404);
})->where('any', '.*');
