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
            'artist_id' => 'required|integer',
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
            'artist_id' => $albumData['artist_id'],
        ]);

        return response()->json([
            'message' => 'Album created successfully',
            'data' => $album,
        ]);
    }

    public function getAllAlbums(): JsonResponse
    {
        $albums = Album::all();

        return response()->json([
            'message' => 'Albums retrieved successfully',
            'data' => [
                $albums
            ],
        ]);
    }

    public function getAlbumById($id): JsonResponse
    {
        $album = Album::find($id);
        $song = Song::where('album_id', $id)->get();

        return response()->json([
            'message' => 'Album retrieved successfully',
            'data' => [
                'albums' => $album,
                'songs' => $song
            ],
        ]);
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function updateAlbum(Request $request, $id): JsonResponse
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

        $album = Album::find($id);
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

    public function deleteAlbum($id): JsonResponse
    {
        $album = Album::find($id);
        $album->delete();

        return response()->json([
            'message' => 'Album deleted successfully',
        ]);
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function createSong(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'audio' => 'required|mimes:mp3',
            'duration' => 'required|integer',
            'album_id' => 'required|integer',
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
            'album_id' => $songData['album_id'],
        ]);

        return response()->json([
            'message' => 'Song created successfully',
            'data' => $song,
        ]);
    }

    public function getAllSongs(): JsonResponse
    {
        $songs = Song::all();

        return response()->json([
            'message' => 'Songs retrieved successfully',
            'data' => $songs,
        ]);
    }

    public function getSongById($id): JsonResponse
    {
        $song = Song::find($id);

        return response()->json([
            'message' => 'Song retrieved successfully',
            'data' => $song,
        ]);
    }

    public function deleteSong($id): JsonResponse
    {
        $song = Song::find($id);
        $song->delete();

        return response()->json([
            'message' => 'Song deleted successfully',
        ]);
    }
}
