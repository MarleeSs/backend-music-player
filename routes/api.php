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

Route::post('auth', [\App\Http\Controllers\UserAuthController::class, 'userAuth']);
Route::post('artist-auth', [\App\Http\Controllers\ArtistAuthController::class, 'artistAuth']);

Route::middleware(['api.user'])->prefix('user')->group(function (){
    Route::get('home', [\App\Http\Controllers\UserController::class, 'home']);
    Route::post('like/{songId}', [\App\Http\Controllers\UserController::class, 'likeSong']);
    Route::post('unlike/{songId}', [\App\Http\Controllers\UserController::class, 'unlikeSong']);
});

Route::middleware(['api.artist'])->prefix('artist')->group(function (){

    // CRUD Album
    Route::post('albums', [\App\Http\Controllers\ArtistController::class, 'createAlbum']);
    Route::get('albums', [\App\Http\Controllers\ArtistController::class, 'getAllAlbums']);
    Route::get('albums/{id}', [\App\Http\Controllers\ArtistController::class, 'getAlbumById']);
    Route::patch('albums/{id}', [\App\Http\Controllers\ArtistController::class, 'updateAlbum']);
    Route::delete('albums/{id}', [\App\Http\Controllers\ArtistController::class, 'deleteAlbum']);

    // CRUD Song
    Route::post('songs', [\App\Http\Controllers\ArtistController::class, 'createSong']);
    Route::get('songs', [\App\Http\Controllers\ArtistController::class, 'getAllSongs']);
    Route::get('songs/{id}', [\App\Http\Controllers\ArtistController::class, 'getSongById']);
    Route::delete('songs/{id}', [\App\Http\Controllers\ArtistController::class, 'deleteSong']);
});
