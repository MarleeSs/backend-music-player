<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/auth', [\App\Http\Controllers\UserAuthController::class, 'userAuth']);
Route::post('/artist-auth', [\App\Http\Controllers\ArtistAuthController::class, 'artistAuth']);

Route::middleware(['api.user'])->prefix('user')->group(function () {
    Route::get('/home', [\App\Http\Controllers\UserController::class, 'home']);
    Route::post('/like/{songId}', [\App\Http\Controllers\UserController::class, 'likeSong']);
    Route::post('/unlike/{songId}', [\App\Http\Controllers\UserController::class, 'unlikeSong']);
    Route::get('/songs/like', [\App\Http\Controllers\UserController::class, 'getLikeSongs']);
    Route::get('/account', [\App\Http\Controllers\UserController::class, 'getAccount']);
    Route::get('/search', [\App\Http\Controllers\UserController::class, 'search']);
});

Route::middleware(['api.artist'])->prefix('artist')->group(function () {
    Route::prefix('/albums')->group(function () {

        // CRUD Album
        Route::post('/', [\App\Http\Controllers\ArtistController::class, 'createAlbum']);
        Route::get('/', [\App\Http\Controllers\ArtistController::class, 'getAllAlbums']);
        Route::get('/{albumId}', [\App\Http\Controllers\ArtistController::class, 'getAlbumById']);
        Route::patch('/{albumId}', [\App\Http\Controllers\ArtistController::class, 'updateAlbum']);
        Route::delete('/{albumId}', [\App\Http\Controllers\ArtistController::class, 'deleteAlbum']);

        // CRUD Song
        Route::post('/{albumId}/songs', [\App\Http\Controllers\ArtistController::class, 'createSong']);
        Route::get('/{albumId}/songs', [\App\Http\Controllers\ArtistController::class, 'getAllSongs']);
        Route::get('/{albumId}/songs/{songId}', [\App\Http\Controllers\ArtistController::class, 'getSongById']);
        Route::delete('/{albumId}/songs/{songId}', [\App\Http\Controllers\ArtistController::class, 'deleteSong']);
    });
});

Route::middleware(['api.admin'])->prefix('admin')->group(function () {
    Route::post('/approve/{songId}', [\App\Http\Controllers\AdminController::class, 'approve']);
});
