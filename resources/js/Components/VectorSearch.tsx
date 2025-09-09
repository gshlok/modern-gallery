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
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '',
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '',
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
        <div className=\"max-w-7xl mx-auto p-6\">
            {/* Header */}
            <div className=\"mb-8\">
                <h1 className=\"text-3xl font-bold text-gray-900 mb-2\">Vector Search</h1>
                <p className=\"text-gray-600\">Search for images using AI-powered semantic similarity</p>
            </div>

            {error && (
                <div className=\"mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700\">
                    {error}
                </div>
            )}

            {/* Search Form */}
            <div className=\"bg-white rounded-lg shadow-lg p-6 mb-8\">
                <form onSubmit={handleSemanticSearch} className=\"space-y-4\">
                    <div>
                        <label className=\"block text-sm font-medium text-gray-700 mb-2\">
                            Semantic Search Query
                        </label>
                        <div className=\"flex gap-4\">
                            <input
                                type=\"text\"
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className=\"flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent\"
                                placeholder=\"e.g., 'sunset over mountains', 'person wearing red dress', 'vintage car'\"
                            />
                            <button
                                type=\"submit\"
                                disabled={isSearching || !searchQuery.trim()}
                                className=\"bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors\"
                            >
                                {isSearching ? 'Searching...' : 'Search'}
                            </button>
                        </div>
                    </div>

                    <div className=\"flex items-center gap-4\">
                        <label className=\"text-sm font-medium text-gray-700\">
                            Similarity Threshold: {searchThreshold}
                        </label>
                        <input
                            type=\"range\"
                            min=\"0.5\"
                            max=\"1\"
                            step=\"0.05\"
                            value={searchThreshold}
                            onChange={(e) => setSearchThreshold(parseFloat(e.target.value))}
                            className=\"flex-1 max-w-xs\"
                        />
                    </div>
                </form>
            </div>

            {/* Embedding Status */}
            {embeddingStatus && (
                <div className=\"bg-white rounded-lg shadow-lg p-6 mb-8\">
                    <h3 className=\"text-lg font-semibold text-gray-800 mb-4\">Embedding Status</h3>
                    <div className=\"grid grid-cols-1 md:grid-cols-3 gap-4 text-center\">
                        <div className=\"p-4 bg-blue-50 rounded-lg\">
                            <div className=\"text-2xl font-bold text-blue-600\">{embeddingStatus.total_images}</div>
                            <div className=\"text-sm text-gray-600\">Total Images</div>
                        </div>
                        <div className=\"p-4 bg-green-50 rounded-lg\">
                            <div className=\"text-2xl font-bold text-green-600\">{embeddingStatus.with_embeddings}</div>
                            <div className=\"text-sm text-gray-600\">With Embeddings</div>
                        </div>
                        <div className=\"p-4 bg-yellow-50 rounded-lg\">
                            <div className=\"text-2xl font-bold text-yellow-600\">{embeddingStatus.without_embeddings}</div>
                            <div className=\"text-sm text-gray-600\">Need Embeddings</div>
                        </div>
                    </div>
                </div>
            )}

            <div className=\"grid grid-cols-1 lg:grid-cols-2 gap-8\">
                {/* Search Results */}
                <div className=\"bg-white rounded-lg shadow-lg p-6\">
                    <h3 className=\"text-xl font-bold mb-4 text-gray-800\">Search Results</h3>
                    
                    {searchResults.length === 0 ? (
                        <p className=\"text-gray-500 text-center py-8\">
                            {isSearching ? 'Searching...' : 'Enter a search query to find similar images'}
                        </p>
                    ) : (
                        <div className=\"space-y-4 max-h-96 overflow-y-auto\">
                            {searchResults.map((result) => (
                                <div
                                    key={result.id}
                                    className=\"flex items-center gap-4 p-3 border rounded-lg hover:bg-gray-50 transition-colors\"
                                >
                                    <img
                                        src={result.thumbnail_url}
                                        alt={result.title}
                                        className=\"w-16 h-16 object-cover rounded-lg\"
                                    />
                                    <div className=\"flex-1\">
                                        <h4 className=\"font-medium text-gray-900\">{result.title}</h4>
                                        <p className=\"text-sm text-gray-600 line-clamp-2\">{result.description}</p>
                                        <div className=\"flex items-center gap-4 mt-1\">
                                            <span className={`text-sm font-medium ${getSimilarityColor(result.similarity_score)}`}>
                                                {getSimilarityLabel(result.similarity_score)} ({(result.similarity_score * 100).toFixed(1)}%)
                                            </span>
                                            <span className=\"text-xs text-gray-500\">by {result.user.name}</span>
                                        </div>
                                    </div>
                                    <button
                                        onClick={() => handleFindSimilar(result.id, result.url.split('/').pop() || '')}
                                        className=\"bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700 transition-colors\"
                                        disabled={isFindingSimilar}
                                    >
                                        Find Similar
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* Similar Images */}
                <div className=\"bg-white rounded-lg shadow-lg p-6\">
                    <h3 className=\"text-xl font-bold mb-4 text-gray-800\">Similar Images</h3>
                    
                    {selectedImage && (
                        <div className=\"mb-4 p-3 bg-blue-50 rounded-lg\">
                            <div className=\"flex items-center gap-3\">
                                <img
                                    src={selectedImage.thumbnail_url}
                                    alt={selectedImage.title}
                                    className=\"w-12 h-12 object-cover rounded-lg\"
                                />
                                <div>
                                    <p className=\"font-medium text-blue-900\">Finding similar to:</p>
                                    <p className=\"text-sm text-blue-700\">{selectedImage.title}</p>
                                </div>
                            </div>
                        </div>
                    )}
                    
                    {similarImages.length === 0 ? (
                        <p className=\"text-gray-500 text-center py-8\">
                            {isFindingSimilar ? 'Finding similar images...' : 'Click \"Find Similar\" on any image to see related images'}
                        </p>
                    ) : (
                        <div className=\"space-y-4 max-h-96 overflow-y-auto\">
                            {similarImages.map((image) => (
                                <div
                                    key={image.id}
                                    className=\"flex items-center gap-4 p-3 border rounded-lg hover:bg-gray-50 transition-colors\"
                                >
                                    <img
                                        src={image.thumbnail_url}
                                        alt={image.title}
                                        className=\"w-16 h-16 object-cover rounded-lg\"
                                    />
                                    <div className=\"flex-1\">
                                        <h4 className=\"font-medium text-gray-900\">{image.title}</h4>
                                        <span className={`text-sm font-medium ${getSimilarityColor(image.similarity_score)}`}>
                                            {getSimilarityLabel(image.similarity_score)} ({(image.similarity_score * 100).toFixed(1)}%)
                                        </span>
                                    </div>
                                    <button
                                        onClick={() => handleFindSimilar(image.id, image.url.split('/').pop() || '')}
                                        className=\"bg-purple-600 text-white px-3 py-1 rounded text-sm hover:bg-purple-700 transition-colors\"
                                        disabled={isFindingSimilar}
                                    >
                                        Find Similar
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {/* Vector Search Info */}
            <div className=\"mt-8 bg-gray-50 rounded-lg p-6\">
                <h3 className=\"text-lg font-semibold text-gray-800 mb-3\">How Vector Search Works</h3>
                <div className=\"text-sm text-gray-600 space-y-2\">
                    <p>• <strong>Semantic Search:</strong> Find images based on meaning rather than just keywords</p>
                    <p>• <strong>AI Embeddings:</strong> Each image is converted to a high-dimensional vector representing its visual features</p>
                    <p>• <strong>Similarity Matching:</strong> Compare vectors to find visually or conceptually similar images</p>
                    <p>• <strong>Threshold Control:</strong> Adjust the similarity threshold to get more or fewer results</p>
                </div>
            </div>
        </div>
    );
};

export default VectorSearch;