<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Song;
use App\Models\TransactionPlaylist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ArtistController extends Controller
{
    /**
     * @throws \Exception
     */
    public function createAlbum(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }

        $image = $request->file('image');

        $fileName = now()->timestamp . '_' . $request->image->getClientOriginalName();
        $image->move('uploads', $fileName);

        $albumData = $validator->validated();

        $album = Album::create([
            'title' => $albumData['title'],
            'image' => 'uploads/' . $fileName,
            'artist_id' => $request->userId,
        ]);

        return response()->json([
            'message' => 'Album created successfully',
            'data' => [
                'id' => $album->id,
                'title' => $album->title,
                'image' => url($album->image),
                'artistId' => $album->artist_id,
            ],
        ]);
    }

    public function getAllAlbums(Request $request): JsonResponse
    {
        $artistId = $request->userId;
        $albums = Album::where('artist_id', $artistId)->get();
        $data = [];
        foreach ($albums as $album) {
            $songs = Song::where('album_id', $album->id)->get();
            $songData = [];
            foreach ($songs as $song) {
                $songData[] = [
                    'id' => $song->id,
                    'title' => $song->title,
                    'likes' => $song->likes,
                    'duration' => $song->duration,
                    'status' => $song->status,
                    'release' => $song->created_at,
                    'audioUrl' => url($song->audio_path),
                    'albumId' => $song->album_id,
                ];
            }

            $data[] = [
                'id' => $album->id,
                'title' => $album->title,
                'image' => url($album->image),
                'songs' => $songData
            ];
        }

        return response()->json([
            'message' => 'Albums retrieved successfully',
            'data' => $data
        ]);
    }

    public function getAlbumById(Request $request, $albumId): JsonResponse
    {
        $artistId = $request->userId;
        if (Album::where('artist_id', $artistId)->where('id', $albumId)->doesntExist()) {
            return response()->json([
                'message' => 'Album not found',
                'data' => null,
            ], 404);
        }

        $album = Album::where('artist_id', $artistId)->where('id', $albumId)->first();
        $songs = Song::where('album_id', $album->id)->get();
        $songData = [];
        foreach ($songs as $song) {
            $songData[] = [
                'id' => $song->id,
                'title' => $song->title,
                'likes' => $song->likes,
                'duration' => $song->duration,
                'status' => $song->status,
                'release' => $song->created_at,
                'audioUrl' => url($song->audio_path),
                'albumId' => $song->album_id,
            ];
        }

        return response()->json([
            'message' => 'Album retrieved successfully',
            'data' => [
                'id' => $album->id,
                'title' => $album->title,
                'image' => url($album->image),
                'publishDate' => $album->created_at,
                'songs' => $songData
            ]
        ]);
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function updateAlbum(Request $request, $albumId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'string',
            'image' => 'image|mimes:jpeg,png,jpg',
            'artist_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }

        $albumData = $validator->validated();

        $album = Album::find($albumId);
        if (isset($albumData['title'])) {
            $album->title = $albumData['title'];
        }
        if (isset($albumData['image'])) {
            $image = $request->file('image');
            $fileName = now()->timestamp . '_' . $request->image->getClientOriginalName();
            $image->move('uploads', $fileName);
            $album->image = 'uploads/' . $fileName;
        }
        if (isset($albumData['artist_id'])) {
            $album->artist_id = $albumData['artist_id'];
        }
        $album->save();

        return response()->json([
            'message' => 'Album updated successfully',
            'data' => $album,
        ]);
    }

    public function deleteAlbum($albumId): JsonResponse
    {
        $album = Album::find($albumId);
        $album->delete();

        return response()->json([
            'message' => 'Album deleted successfully',
        ]);
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function createSong(Request $request, $albumId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'audio' => 'required|mimes:mp3,mpeg',
            'duration' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return messageError($validator->messages()->toArray());
        }

        $audio = $request->file('audio');

        $fileName = now()->timestamp . '_' . $request->audio->getClientOriginalName();
        $audio->move('uploads', $fileName);

        $songData = $validator->validated();

        $song = Song::create([
            'title' => $songData['title'],
            'audio_path' => 'uploads/' . $fileName,
            'duration' => $songData['duration'],
            'album_id' => $albumId,
        ]);

        return response()->json([
            'message' => 'Song created successfully',
            'data' => $song,
        ]);
    }

    public function getAllSongs(Request $request, $albumId): JsonResponse
    {
        $artistId = $request->userId;
        if (Album::where('artist_id', $artistId)->where('id', $albumId)->doesntExist()) {
            return response()->json([
                'message' => 'Album not found',
                'data' => null,
            ], 404);
        }

        $songs = Song::where('album_id', $albumId)->get();

        return response()->json([
            'message' => 'Songs retrieved successfully',
            'data' => $songs,
        ]);
    }

    public function getSongById(Request $request, $albumId, $songId): JsonResponse
    {
        $artistId = $request->userId;

        if (Album::where('artist_id', $artistId)->where('id', $albumId)->doesntExist()) {
            return response()->json([
                'message' => 'Album not found',
                'data' => null,
            ], 404);
        }

        if (Song::where('album_id', $albumId)->where('id', $songId)->doesntExist()) {
            return response()->json([
                'message' => 'Song not found',
                'data' => null,
            ], 404);
        }

        $song = Song::where('album_id', $albumId)->where('id', $songId)->first();

        return response()->json([
            'message' => 'Song retrieved successfully',
            'data' => $song,
        ]);
    }

    public function deleteSong(Request $request, $albumId, $songId): JsonResponse
    {
        $artistId = $request->userId;

        if (Album::where('artist_id', $artistId)->where('id', $albumId)->doesntExist()) {
            return response()->json([
                'message' => 'Album not found',
                'data' => null,
            ], 404);
        }

        if (Song::where('album_id', $albumId)->where('id', $songId)->doesntExist()) {
            return response()->json([
                'message' => 'Song not found',
                'data' => null,
            ], 404);
        }

        $song = Song::where('album_id', $albumId)->where('id', $songId)->first();
        $song->delete();

        return response()->json([
            'message' => 'Song deleted successfully',
        ]);
    }
}
