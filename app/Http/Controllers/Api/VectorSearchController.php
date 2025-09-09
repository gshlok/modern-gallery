<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\VectorEmbedding;
use App\Services\VectorSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * Vector Search Controller
 * 
 * Handles semantic search and similarity matching for images.
 * This is a placeholder implementation that will be connected to actual
 * vector database services like Pinecone, Weaviate, or pgvector.
 */
class VectorSearchController extends Controller
{
    protected VectorSearchService $vectorService;

    public function __construct(VectorSearchService $vectorService)
    {
        $this->vectorService = $vectorService;
    }

    /**
     * Perform semantic search on images
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:500',
            'limit' => 'sometimes|integer|min:1|max:100',
            'threshold' => 'sometimes|numeric|min:0|max:1',
            'user_id' => 'sometimes|exists:users,id',
            'album_id' => 'sometimes|exists:albums,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $results = $this->vectorService->semanticSearch(
                query: $request->input('query'),
                limit: $request->input('limit', 20),
                threshold: $request->input('threshold', 0.7),
                userId: $request->input('user_id'),
                albumId: $request->input('album_id')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'total' => count($results),
                    'query' => $request->input('query'),
                    'threshold' => $request->input('threshold', 0.7),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find similar images to a given image
     * 
     * @param Request $request
     * @param Image $image
     * @return JsonResponse
     */
    public function similar(Request $request, Image $image): JsonResponse
    {
        // Check if user can view the source image
        if (!$image->canView(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found or access denied',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'limit' => 'sometimes|integer|min:1|max:50',
            'threshold' => 'sometimes|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $similarImages = $this->vectorService->findSimilarImages(
                image: $image,
                limit: $request->input('limit', 10),
                threshold: $request->input('threshold', 0.8)
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'source_image' => [
                        'id' => $image->id,
                        'title' => $image->title,
                        'url' => $image->url,
                        'thumbnail_url' => $image->thumbnail_url,
                    ],
                    'similar_images' => $similarImages,
                    'total' => count($similarImages),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Similarity search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate embeddings for an image
     * 
     * @param Request $request
     * @param Image $image
     * @return JsonResponse
     */
    public function generateEmbedding(Request $request, Image $image): JsonResponse
    {
        // Check permissions
        if (!$image->canEdit(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied',
            ], 403);
        }

        try {
            $embedding = $this->vectorService->generateImageEmbedding($image);

            return response()->json([
                'success' => true,
                'data' => [
                    'image_id' => $image->id,
                    'embedding_id' => $embedding->id,
                    'dimensions' => $embedding->dimensions,
                    'model' => $embedding->model,
                    'generated_at' => $embedding->created_at,
                ],
                'message' => 'Embedding generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Embedding generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Batch generate embeddings for multiple images
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function batchGenerateEmbeddings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image_ids' => 'required|array|min:1|max:100',
            'image_ids.*' => 'exists:images,id',
            'force_regenerate' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $imageIds = $request->input('image_ids');
        $forceRegenerate = $request->input('force_regenerate', false);

        // Filter images user can edit
        $images = Image::whereIn('id', $imageIds)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('user', function ($q) use ($user) {
                        return $user->canModerate();
                    });
            })
            ->get();

        if ($images->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No accessible images found',
            ], 404);
        }

        try {
            $results = $this->vectorService->batchGenerateEmbeddings(
                images: $images,
                forceRegenerate: $forceRegenerate
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'processed' => count($results['success']),
                    'failed' => count($results['failed']),
                    'skipped' => count($results['skipped']),
                    'details' => $results,
                ],
                'message' => 'Batch embedding generation completed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch embedding generation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get embedding status for images
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function embeddingStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image_ids' => 'sometimes|array',
            'image_ids.*' => 'exists:images,id',
            'user_id' => 'sometimes|exists:users,id',
            'album_id' => 'sometimes|exists:albums,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $status = $this->vectorService->getEmbeddingStatus(
                imageIds: $request->input('image_ids'),
                userId: $request->input('user_id'),
                albumId: $request->input('album_id')
            );

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get embedding status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get vector search statistics
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->vectorService->getSearchStats();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get search statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test vector search service connectivity
     * 
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $health = $this->vectorService->healthCheck();

            return response()->json([
                'success' => true,
                'data' => $health,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vector search service health check failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
