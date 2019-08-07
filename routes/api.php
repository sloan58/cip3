<?php

use App\Models\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Get the supported image files for a particular phone model
Route::get('/phone-images/{phoneName}', function($phoneName) {
    $images = Phone::where('name', $phoneName)->first()->getAvailableImages();
    return response([
        'images' => $images
    ], 200);
});
