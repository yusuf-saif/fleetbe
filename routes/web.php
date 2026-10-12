<?php

use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get("/", [StatusController::class, "status"]);

Route::get('/swagger', function () {
    return view('swagger');
})->name('swagger');

Route::get('/docs/swagger', function () {
    return view('swagger');
});

Route::get('/api/documentation', function () {
    return view('swagger');
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    return 'Cache cleared.';
});
