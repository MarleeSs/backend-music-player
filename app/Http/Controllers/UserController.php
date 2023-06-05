<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\TransactionPlaylist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function home(): JsonResponse
    {
        $songs = Song::orderBy('likes', 'desc')->take(100)->get();

        return response()->json([
            'message' => 'Songs retrieved successfully',
            'data' => [
                'songs' => $songs
            ],
        ]);
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function likeSong(Request $request, $songId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer'
        ]);

        $playlist = Playlist::where('user_id', $request->user_id)->first();

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }

        $data = TransactionPlaylist::create([
            'playlist_id' => $playlist->id,
            'song_id' => $songId
        ]);

        Song::where('id', $songId)->update([
            'likes' => TransactionPlaylist::where('song_id', $songId)->count()
        ]);

        return response()->json([
            'message' => 'Successfully created transaction playlist',
            'data' => $data
        ], 201);
    }

    /**
     * @throws \Exception
     */
    public function unlikeSong(Request $request, $songId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer'
        ]);

        $playlist = Playlist::where('user_id', $request->user_id)->first();

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }

        $data = TransactionPlaylist::where('playlist_id', $playlist->id)
            ->where('song_id', $songId)
            ->delete();

        Song::where('id', $songId)->update([
            'likes' => TransactionPlaylist::where('song_id', $songId)->count()
        ]);

        return response()->json([
            'message' => 'Successfully created transaction playlist',
            'data' => $data
        ], 201);
    }
}
