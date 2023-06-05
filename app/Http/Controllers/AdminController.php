<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function approve($songId): JsonResponse
    {
        $song = Song::where('id', $songId)->update([
            'status' => 'published'
        ]);
        return response()->json([
            'message' => 'Successfully approved song',
            'data' => $song
        ], 201);
    }

    public function reject($songId): JsonResponse
    {
        $song = Song::where('id', $songId)->update([
            'status' => 'rejected'
        ]);
        return response()->json([
            'message' => 'Successfully rejected song',
            'data' => $song
        ], 201);
    }
}
