import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';

interface VectorSearchProps {
    user: any;
}

interface SearchResult {
    id: number;
    title: string;
    description: string;
    url: string;
    thumbnail_url: string;
    similarity_score: number;
    user: {
        id: number;
        name: string;
    };
}

interface SimilarImage {
    id: number;
    title: string;
    url: string;
    thumbnail_url: string;
    similarity_score: number;
}

const VectorSearch: React.FC<VectorSearchProps> = ({ user }) => {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<SearchResult[]>([]);
    const [similarImages, setSimilarImages] = useState<SimilarImage[]>([]);
    const [selectedImage, setSelectedImage] = useState<any>(null);
    const [isSearching, setIsSearching] = useState(false);
    const [isFindingSimilar, setIsFindingSimilar] = useState(false);
    const [searchThreshold, setSearchThreshold] = useState(0.7);
    const [error, setError] = useState('');
    const [embeddingStatus, setEmbeddingStatus] = useState<any>(null);

    useEffect(() => {
        fetchEmbeddingStatus();
    }, []);

    const fetchEmbeddingStatus = async () => {
        try {
            const response = await fetch('/api/v1/vector/embeddings/status');
            const data = await response.json();
            
            if (data.success) {
                setEmbeddingStatus(data.data);
            }
        } catch (err) {
            console.error('Failed to fetch embedding status:', err);
        }
    };

    const handleSemanticSearch = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!searchQuery.trim()) return;

        setIsSearching(true);
        setError('');

        try {
            const response = await fetch('/api/v1/vector/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    query: searchQuery,
                    threshold: searchThreshold,
                    limit: 20,
                }),
            });

            const data = await response.json();

            if (data.success) {
                setSearchResults(data.data.results || []);
            } else {
                setError(data.message || 'Search failed');
                setSearchResults([]);
            }
        } catch (err) {
            setError('Failed to perform search. Please try again.');
            setSearchResults([]);
        } finally {
            setIsSearching(false);
        }
    };

    const handleFindSimilar = async (imageId: number, imageSlug: string) => {
        setIsFindingSimilar(true);
        setError('');

        try {
            const response = await fetch(`/api/v1/vector/similar/${imageSlug}?threshold=${searchThreshold}&limit=10`);
            const data = await response.json();

            if (data.success) {
                setSimilarImages(data.data.similar_images || []);
                setSelectedImage(data.data.source_image);
            } else {
                setError(data.message || 'Failed to find similar images');
                setSimilarImages([]);
            }
        } catch (err) {
            setError('Failed to find similar images. Please try again.');
            setSimilarImages([]);
        } finally {
            setIsFindingSimilar(false);
        }
    };

    const handleGenerateEmbedding = async (imageId: number, imageSlug: string) => {
        try {
            const response = await fetch(`/api/v1/vector/embeddings/generate/${imageSlug}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            const data = await response.json();

            if (data.success) {
                fetchEmbeddingStatus();
                alert('Embedding generated successfully!');
            } else {
                setError(data.message || 'Failed to generate embedding');
            }
        } catch (err) {
            setError('Failed to generate embedding. Please try again.');
        }
    };

    const getSimilarityColor = (score: number) => {
        if (score >= 0.9) return 'text-green-600';
        if (score >= 0.8) return 'text-blue-600';
        if (score >= 0.7) return 'text-yellow-600';
        return 'text-gray-600';
    };

    const getSimilarityLabel = (score: number) => {
        if (score >= 0.9) return 'Very Similar';
        if (score >= 0.8) return 'Similar';
        if (score >= 0.7) return 'Somewhat Similar';
        return 'Different';
    };

    return (
        <div className="max-w-7xl mx-auto p-6">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-gray-900 mb-2">Vector Search</h1>
                <p className="text-gray-600">Search for images using AI-powered semantic similarity</p>
            </div>

            {error && (
                <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    {error}
                </div>
            )}

            {/* Search Form */}
            <div className="bg-white rounded-lg shadow-lg p-6 mb-8">
                <form onSubmit={handleSemanticSearch} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Semantic Search Query
                        </label>
                        <div className="flex gap-4">
                            <input
                                type="text"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="e.g., 'sunset over mountains', 'person wearing red dress', 'vintage car'"
                            />
                            <button
                                type="submit"
                                disabled={isSearching || !searchQuery.trim()}
                                className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                {isSearching ? 'Searching...' : 'Search'}
                            </button>
                        </div>
                    </div>

                    <div className="flex items-center gap-4">
                        <label className="text-sm font-medium text-gray-700">
                            Similarity Threshold: {searchThreshold}
                        </label>
                        <input
                            type="range"
                            min="0.5"
                            max="1"
                            step="0.1"
                            value={searchThreshold}
                            onChange={(e) => setSearchThreshold(parseFloat(e.target.value))}
                            className="flex-1 max-w-xs"
                        />
                    </div>
                </form>
            </div>

            {/* Embedding Status */}
            {embeddingStatus && (
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                    <h3 className="text-lg font-medium text-blue-900 mb-2">Embedding Status</h3>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span className="font-medium">Total Images:</span> {embeddingStatus.total_images || 0}
                        </div>
                        <div>
                            <span className="font-medium">With Embeddings:</span> {embeddingStatus.images_with_embeddings || 0}
                        </div>
                        <div>
                            <span className="font-medium">Coverage:</span> {embeddingStatus.coverage_percentage || 0}%
                        </div>
                        <div>
                            <span className="font-medium">Last Updated:</span> {embeddingStatus.last_updated || 'Never'}
                        </div>
                    </div>
                </div>
            )}

            {/* Search Results */}
            {searchResults.length > 0 && (
                <div className="bg-white rounded-lg shadow-lg p-6 mb-8">
                    <h3 className="text-xl font-bold mb-4 text-gray-800">
                        Search Results ({searchResults.length})
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {searchResults.map((result) => (
                            <div key={result.id} className="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                <div className="aspect-square">
                                    <img
                                        src={result.thumbnail_url}
                                        alt={result.title}
                                        className="w-full h-full object-cover"
                                    />
                                </div>
                                <div className="p-4">
                                    <h4 className="font-medium text-gray-900 truncate mb-1">
                                        {result.title}
                                    </h4>
                                    <p className="text-sm text-gray-600 truncate mb-2">
                                        by {result.user.name}
                                    </p>
                                    <div className="flex items-center justify-between">
                                        <span className={`text-sm font-medium ${getSimilarityColor(result.similarity_score)}`}>
                                            {getSimilarityLabel(result.similarity_score)}
                                        </span>
                                        <span className="text-xs text-gray-500">
                                            {Math.round(result.similarity_score * 100)}%
                                        </span>
                                    </div>
                                    <button
                                        onClick={() => handleFindSimilar(result.id, result.url.split('/').pop() || '')}
                                        className="mt-2 w-full text-sm bg-gray-100 text-gray-700 py-1 px-2 rounded hover:bg-gray-200 transition-colors"
                                    >
                                        Find Similar
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Similar Images */}
            {selectedImage && similarImages.length > 0 && (
                <div className="bg-white rounded-lg shadow-lg p-6">
                    <h3 className="text-xl font-bold mb-4 text-gray-800">
                        Images Similar to "{selectedImage.title}"
                    </h3>
                    
                    {/* Source Image */}
                    <div className="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h4 className="font-medium text-gray-700 mb-2">Source Image:</h4>
                        <div className="flex items-center gap-4">
                            <img
                                src={selectedImage.thumbnail_url}
                                alt={selectedImage.title}
                                className="w-20 h-20 object-cover rounded"
                            />
                            <div>
                                <p className="font-medium">{selectedImage.title}</p>
                                <p className="text-sm text-gray-600">{selectedImage.description}</p>
                            </div>
                        </div>
                    </div>

                    {/* Similar Images Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {similarImages.map((image) => (
                            <div key={image.id} className="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                                <div className="aspect-square">
                                    <img
                                        src={image.thumbnail_url}
                                        alt={image.title}
                                        className="w-full h-full object-cover"
                                    />
                                </div>
                                <div className="p-4">
                                    <h4 className="font-medium text-gray-900 truncate mb-1">
                                        {image.title}
                                    </h4>
                                    <div className="flex items-center justify-between">
                                        <span className={`text-sm font-medium ${getSimilarityColor(image.similarity_score)}`}>
                                            {getSimilarityLabel(image.similarity_score)}
                                        </span>
                                        <span className="text-xs text-gray-500">
                                            {Math.round(image.similarity_score * 100)}%
                                        </span>
                                    </div>
                                    <button
                                        onClick={() => handleFindSimilar(image.id, image.url.split('/').pop() || '')}
                                        className="mt-2 w-full text-sm bg-gray-100 text-gray-700 py-1 px-2 rounded hover:bg-gray-200 transition-colors"
                                    >
                                        Find Similar
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* No Results */}
            {searchResults.length === 0 && searchQuery && !isSearching && (
                <div className="bg-white rounded-lg shadow-lg p-6 text-center">
                    <p className="text-gray-500">No images found matching your search query.</p>
                    <p className="text-sm text-gray-400 mt-1">Try adjusting your search terms or lowering the similarity threshold.</p>
                </div>
            )}
        </div>
    );
};

export default VectorSearch;