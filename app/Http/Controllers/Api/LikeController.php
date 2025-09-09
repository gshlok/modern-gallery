<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Image;
use App\Models\Album;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    /**
     * Like an image.
     */
    public function likeImage(Image $image): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $like = Like::firstOrCreate([
            'user_id' => $user->id,
            'likeable_type' => Image::class,
            'likeable_id' => $image->id,
        ]);

        return response()->json(['message' => 'Image liked', 'like' => $like]);
    }

    /**
     * Unlike an image.
     */
    public function unlikeImage(Image $image): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Like::where([
            'user_id' => $user->id,
            'likeable_type' => Image::class,
            'likeable_id' => $image->id,
        ])->delete();

        return response()->json(['message' => 'Image unliked']);
    }

    /**
     * Like an album.
     */
    public function likeAlbum(Album $album): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $like = Like::firstOrCreate([
            'user_id' => $user->id,
            'likeable_type' => Album::class,
            'likeable_id' => $album->id,
        ]);

        return response()->json(['message' => 'Album liked', 'like' => $like]);
    }

    /**
     * Unlike an album.
     */
    public function unlikeAlbum(Album $album): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Like::where([
            'user_id' => $user->id,
            'likeable_type' => Album::class,
            'likeable_id' => $album->id,
        ])->delete();

        return response()->json(['message' => 'Album unliked']);
    }

    /**
     * Like a comment.
     */
    public function likeComment(Comment $comment): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $like = Like::firstOrCreate([
            'user_id' => $user->id,
            'likeable_type' => Comment::class,
            'likeable_id' => $comment->id,
        ]);

        return response()->json(['message' => 'Comment liked', 'like' => $like]);
    }

    /**
     * Unlike a comment.
     */
    public function unlikeComment(Comment $comment): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Like::where([
            'user_id' => $user->id,
            'likeable_type' => Comment::class,
            'likeable_id' => $comment->id,
        ])->delete();

        return response()->json(['message' => 'Comment unliked']);
    }
}
