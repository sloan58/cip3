<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('admin/login');
});

Route::get('storage/{filename}', function($filename) {
    return Storage::download($filename);
});
Route::post('/testmodal', function(\Illuminate\Http\Request $request) {

    \Log::info('Got here!', [$request->all()]);
    dump(backpack_user()->name);
    \Alert::success('Report submitted.')->flash();
    return redirect()->back();

});
