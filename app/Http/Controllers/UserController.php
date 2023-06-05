<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\TransactionPlaylist;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function home(): JsonResponse
    {
        $songs = Song::orderBy('likes', 'desc')->take(100)->get();
        $albums = Album::whereIn('id', $songs->pluck('album_id'))->get();
        $artists = Artist::whereIn('id', $albums->pluck('artist_id'))->get();

        $data = [];
        foreach ($songs as $song) {
            $data[] = [
                'artist' => [
                    'id' => $artists->where('id', $albums->where('id', $song->album_id)->first()->artist_id)->first()->id,
                    'fullName' => $artists->where('id', $albums->where('id', $song->album_id)->first()->artist_id)->first()->full_name,
                ],
                'album' => [
                    'id' => $albums->where('id', $song->album_id)->first()->id,
                    'title' => $albums->where('id', $song->album_id)->first()->title,
                    'image' => url($albums->where('id', $song->album_id)->first()->image),
                ],
                'id' => $song->id,
                'title' => $song->title,
                'likes' => $song->likes,
                'duration' => $song->duration,
                'audioUrl' => url($song->audio_path),
            ];
        }

        return response()->json([
            'message' => 'Songs retrieved successfully',
            'data' => $data
        ]);
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function likeSong(Request $request, $songId): JsonResponse
    {

        $playlist = Playlist::where('user_id', $request->userId)->first();

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
        $playlist = Playlist::where('user_id', $request->userId)->first();

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

    public function getLikeSongs(Request $request): JsonResponse
    {
        $user = $request->userId;
        $playlist = Playlist::where('user_id', $user)->first();
        $trxPlaylist = TransactionPlaylist::where('playlist_id', $playlist->id)->get();
        $songs = Song::whereIn('id', $trxPlaylist->pluck('song_id'))->get();
        $albums = Album::whereIn('id', $songs->pluck('album_id'))->get();
        $artists = Artist::whereIn('id', $albums->pluck('artist_id'))->get();

        $data = [];
        foreach ($songs as $song) {
            $data[] = [
                'artist' => [
                    'id' => $artists->where('id', $albums->where('id', $song->album_id)->first()->artist_id)->first()->id,
                    'fullName' => $artists->where('id', $albums->where('id', $song->album_id)->first()->artist_id)->first()->full_name,
                ],
                'album' => [
                    'id' => $albums->where('id', $song->album_id)->first()->id,
                    'title' => $albums->where('id', $song->album_id)->first()->title,
                    'image' => url($albums->where('id', $song->album_id)->first()->image),
                ],
                'playlist' => [
                    'id' => $trxPlaylist->where('song_id', $song->id)->first()->playlist_id,
                    'songId' => $trxPlaylist->where('song_id', $song->id)->first()->song_id,
                ],
                'id' => $song->id,
                'title' => $song->title,
                'likes' => $song->likes,
                'duration' => $song->duration,
                'audioUrl' => url($song->audio_path),
            ];
        }

        return response()->json([
            'message' => 'Songs retrieved successfully',
            'data' => $data
        ]);
    }

    public function getAccount(Request $request): JsonResponse
    {
        $user = $request->userId;
        $userData = User::where('id', $user)->first();

        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => [
                'userId' => $userData->id,
                'fullName' => $userData->full_name,
                'image' => $userData->image,
            ],
        ]);
    }
}
