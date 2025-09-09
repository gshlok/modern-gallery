<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Image;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /**
     * Display comments for a resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Comment::with(['user', 'commentable']);

        // Filter by commentable type and id
        if ($request->filled('commentable_type') && $request->filled('commentable_id')) {
            $query->where('commentable_type', $request->commentable_type)
                ->where('commentable_id', $request->commentable_id);
        }

        $comments = $query->paginate(20);

        return response()->json($comments);
    }

    /**
     * Store a newly created comment.
     */
    public function store(Request $request): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        // Implementation would go here
        return response()->json(['message' => 'Not implemented'], 501);
    }
}
