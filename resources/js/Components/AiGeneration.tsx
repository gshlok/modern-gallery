import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';

interface AiGenerationProps {
    user: any;
    providers: string[];
}

interface GenerationRequest {
    prompt: string;
    negativePrompt: string;
    provider: string;
    model: string;
    width: number;
    height: number;
    steps: number;
    guidance: number;
    seed: number | null;
    albumId: number | null;
    visibility: 'public' | 'unlisted' | 'private';
}

const AiGeneration: React.FC<AiGenerationProps> = ({ user, providers = [] }) => {
    const [formData, setFormData] = useState<GenerationRequest>({
        prompt: '',
        negativePrompt: '',
        provider: providers[0] || 'openai',
        model: 'dall-e-3',
        width: 1024,
        height: 1024,
        steps: 20,
        guidance: 7.5,
        seed: null,
        albumId: null,
        visibility: 'public',
    });

    const [isGenerating, setIsGenerating] = useState(false);
    const [generations, setGenerations] = useState([]);
    const [error, setError] = useState('');
    const [success, setSuccess] = useState('');

    const providerModels = {
        openai: ['dall-e-3', 'dall-e-2'],
        stability: ['stable-diffusion-xl-1024-v1-0', 'stable-diffusion-v1-6'],
        midjourney: ['midjourney-v6', 'midjourney-v5.2'],
    };

    useEffect(() => {
        fetchGenerations();
    }, []);

    const fetchGenerations = async () => {
        try {
            const response = await fetch('/api/v1/ai/generations');
            const data = await response.json();
            
            if (data.success) {
                setGenerations(data.data.generations || []);
            }
        } catch (err) {
            console.error('Failed to fetch generations:', err);
        }
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsGenerating(true);
        setError('');
        setSuccess('');

        try {
            const response = await fetch('/api/v1/ai/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(formData),
            });

            const data = await response.json();

            if (data.success) {
                setSuccess('Image generation started! Check your generations list for progress.');
                setFormData({ ...formData, prompt: '', negativePrompt: '' });
                fetchGenerations();
            } else {
                setError(data.message || 'Generation failed');
            }
        } catch (err) {
            setError('Failed to start generation. Please try again.');
        } finally {
            setIsGenerating(false);
        }
    };

    const handleInputChange = (field: keyof GenerationRequest, value: any) => {
        setFormData(prev => ({
            ...prev,
            [field]: value,
        }));
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'completed': return 'text-green-600';
            case 'processing': return 'text-blue-600';
            case 'failed': return 'text-red-600';
            default: return 'text-yellow-600';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'completed': return '✅';
            case 'processing': return '⏳';
            case 'failed': return '❌';
            default: return '⏳';
        }
    };

    return (
        <div className="max-w-6xl mx-auto p-6">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* Generation Form */}
                <div className="bg-white rounded-lg shadow-lg p-6">
                    <h2 className="text-2xl font-bold mb-6 text-gray-800">AI Image Generation</h2>
                    
                    {error && (
                        <div className="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                            {error}
                        </div>
                    )}

                    {success && (
                        <div className="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                            {success}
                        </div>
                    )}

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Prompt */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Prompt *
                            </label>
                            <textarea
                                value={formData.prompt}
                                onChange={(e) => handleInputChange('prompt', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                rows={3}
                                placeholder="Describe the image you want to generate..."
                                required
                            />
                        </div>

                        {/* Negative Prompt */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Negative Prompt
                            </label>
                            <textarea
                                value={formData.negativePrompt}
                                onChange={(e) => handleInputChange('negativePrompt', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                rows={2}
                                placeholder="What you don't want in the image..."
                            />
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Provider */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Provider
                                </label>
                                <select
                                    value={formData.provider}
                                    onChange={(e) => {
                                        handleInputChange('provider', e.target.value);
                                        handleInputChange('model', providerModels[e.target.value as keyof typeof providerModels]?.[0] || '');
                                    }}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    {providers.map(provider => (
                                        <option key={provider} value={provider}>
                                            {provider.charAt(0).toUpperCase() + provider.slice(1)}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Model */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Model
                                </label>
                                <select
                                    value={formData.model}
                                    onChange={(e) => handleInputChange('model', e.target.value)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    {(providerModels[formData.provider as keyof typeof providerModels] || []).map(model => (
                                        <option key={model} value={model}>
                                            {model}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Size */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Size
                                </label>
                                <select
                                    value={`${formData.width}x${formData.height}`}
                                    onChange={(e) => {
                                        const [width, height] = e.target.value.split('x').map(Number);
                                        handleInputChange('width', width);
                                        handleInputChange('height', height);
                                    }}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="512x512">512x512</option>
                                    <option value="768x768">768x768</option>
                                    <option value="1024x1024">1024x1024</option>
                                    <option value="1024x768">1024x768</option>
                                    <option value="768x1024">768x1024</option>
                                </select>
                            </div>

                            {/* Steps */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Steps: {formData.steps}
                                </label>
                                <input
                                    type="range"
                                    min="1"
                                    max="50"
                                    value={formData.steps}
                                    onChange={(e) => handleInputChange('steps', parseInt(e.target.value))}
                                    className="w-full"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {/* Guidance Scale */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Guidance: {formData.guidance}
                                </label>
                                <input
                                    type="range"
                                    min="1"
                                    max="20"
                                    step="0.5"
                                    value={formData.guidance}
                                    onChange={(e) => handleInputChange('guidance', parseFloat(e.target.value))}
                                    className="w-full"
                                />
                            </div>

                            {/* Seed */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Seed (optional)
                                </label>
                                <input
                                    type="number"
                                    value={formData.seed || ''}
                                    onChange={(e) => handleInputChange('seed', e.target.value ? parseInt(e.target.value) : null)}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Random"
                                />
                            </div>
                        </div>

                        {/* Visibility */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Visibility
                            </label>
                            <select
                                value={formData.visibility}
                                onChange={(e) => handleInputChange('visibility', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="public">Public</option>
                                <option value="unlisted">Unlisted</option>
                                <option value="private">Private</option>
                            </select>
                        </div>

                        {/* Submit Button */}
                        <button
                            type="submit"
                            disabled={isGenerating || !formData.prompt.trim()}
                            className="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {isGenerating ? (
                                <span className="flex items-center justify-center">
                                    <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Generating...
                                </span>
                            ) : (
                                'Generate Image'
                            )}
                        </button>
                    </form>
                </div>

                {/* Generations History */}
                <div className="bg-white rounded-lg shadow-lg p-6">
                    <h3 className="text-xl font-bold mb-4 text-gray-800">Your Generations</h3>
                    
                    {generations.length === 0 ? (
                        <div className="text-center py-8 text-gray-500">
                            <p>No generations yet. Create your first AI image!</p>
                        </div>
                    ) : (
                        <div className="space-y-4 max-h-96 overflow-y-auto">
                            {generations.map((generation: any) => (
                                <div key={generation.id} className="border border-gray-200 rounded-lg p-4">
                                    <div className="flex items-start justify-between mb-2">
                                        <div className="flex-1">
                                            <p className="font-medium text-sm text-gray-900 truncate">
                                                {generation.prompt}
                                            </p>
                                            <p className="text-xs text-gray-500 mt-1">
                                                {generation.provider} • {generation.model}
                                            </p>
                                        </div>
                                        <span className={`text-sm font-medium ${getStatusColor(generation.status)}`}>
                                            {getStatusIcon(generation.status)} {generation.status}
                                        </span>
                                    </div>
                                    
                                    {generation.image && (
                                        <div className="mt-3">
                                            <img
                                                src={generation.image.url}
                                                alt={generation.prompt}
                                                className="w-full h-32 object-cover rounded"
                                            />
                                        </div>
                                    )}
                                    
                                    {generation.error_message && (
                                        <div className="mt-2 text-xs text-red-600">
                                            Error: {generation.error_message}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default AiGeneration;