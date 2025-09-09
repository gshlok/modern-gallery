import React, { useState, useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';

interface SearchSuggestion {
  text: string;
  type: 'image_title' | 'tag' | 'user' | 'album_title';
  category: string;
  color?: string;
  avatar_url?: string;
}

interface AdvancedSearchProps {
  initialQuery?: string;
  onSearch?: (query: string, filters: any) => void;
  placeholder?: string;
  showAdvanced?: boolean;
}

export default function AdvancedSearch({
  initialQuery = '',
  onSearch,
  placeholder = 'Search images, tags, photographers...',
  showAdvanced = false
}: AdvancedSearchProps) {
  const [query, setQuery] = useState(initialQuery);
  const [suggestions, setSuggestions] = useState<SearchSuggestion[]>([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [loading, setLoading] = useState(false);
  const [showFilters, setShowFilters] = useState(showAdvanced);
  const [filters, setFilters] = useState({
    album_id: '',
    user_id: '',
    tag_id: '',
    license: '',
    date_from: '',
    date_to: '',
    camera: '',
    lens: '',
    featured_only: false,
    has_downloads: false,
  });

  const searchInputRef = useRef<HTMLInputElement>(null);
  const suggestionsRef = useRef<HTMLDivElement>(null);
  const debounceRef = useRef<NodeJS.Timeout>();

  // Fetch suggestions
  const fetchSuggestions = async (searchQuery: string) => {
    if (searchQuery.length < 2) {
      setSuggestions([]);
      return;
    }

    setLoading(true);
    try {
      const response = await fetch(`/api/v1/search/suggestions?q=${encodeURIComponent(searchQuery)}`);
      const data = await response.json();
      setSuggestions(data.suggestions || []);
    } catch (error) {
      console.error('Failed to fetch suggestions:', error);
      setSuggestions([]);
    } finally {
      setLoading(false);
    }
  };

  // Debounced search suggestions
  useEffect(() => {
    if (debounceRef.current) {
      clearTimeout(debounceRef.current);
    }

    debounceRef.current = setTimeout(() => {
      if (query.trim()) {
        fetchSuggestions(query.trim());
      } else {
        setSuggestions([]);
      }
    }, 300);

    return () => {
      if (debounceRef.current) {
        clearTimeout(debounceRef.current);
      }
    };
  }, [query]);

  // Handle search submission
  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setShowSuggestions(false);
    
    if (onSearch) {
      onSearch(query, filters);
    } else {
      // Default behavior - navigate to search page
      const params = new URLSearchParams();
      if (query.trim()) params.set('q', query.trim());
      
      // Add non-empty filters
      Object.entries(filters).forEach(([key, value]) => {
        if (value && value !== '') {
          params.set(key, value.toString());
        }
      });

      router.get(`/search?${params.toString()}`);
    }
  };

  // Handle suggestion selection
  const selectSuggestion = (suggestion: SearchSuggestion) => {
    setQuery(suggestion.text);
    setShowSuggestions(false);
    searchInputRef.current?.focus();
  };

  // Handle keyboard navigation
  const handleKeyDown = (e: React.KeyboardEvent) => {
    if (e.key === 'Escape') {
      setShowSuggestions(false);
      searchInputRef.current?.blur();
    }
  };

  // Handle click outside
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        suggestionsRef.current &&
        !suggestionsRef.current.contains(event.target as Node) &&
        !searchInputRef.current?.contains(event.target as Node)
      ) {
        setShowSuggestions(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div className="relative">
      {/* Search Form */}
      <form onSubmit={handleSearch} className="relative">
        <div className="relative">
          <input
            ref={searchInputRef}
            type="search"
            placeholder={placeholder}
            value={query}
            onChange={(e) => setQuery(e.target.value)}
            onFocus={() => setShowSuggestions(true)}
            onKeyDown={handleKeyDown}
            className="w-full px-4 py-3 pl-12 pr-20 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          />
          
          {/* Search Icon */}
          <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            {loading ? (
              <svg className="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            ) : (
              <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            )}
          </div>

          {/* Action Buttons */}
          <div className="absolute inset-y-0 right-0 flex items-center">
            <button
              type="button"
              onClick={() => setShowFilters(!showFilters)}
              className={`mr-2 p-2 rounded-md transition-colors ${
                showFilters ? 'text-primary-600 bg-primary-50' : 'text-gray-400 hover:text-gray-600'
              }`}
              title="Advanced filters"
            >
              <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
              </svg>
            </button>
            
            <button
              type="submit"
              className="mr-2 px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
            >
              Search
            </button>
          </div>
        </div>

        {/* Suggestions Dropdown */}
        <AnimatePresence>
          {showSuggestions && suggestions.length > 0 && (
            <motion.div
              ref={suggestionsRef}
              initial={{ opacity: 0, y: -10 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -10 }}
              className="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto"
            >
              {suggestions.map((suggestion, index) => (
                <button
                  key={index}
                  type="button"
                  onClick={() => selectSuggestion(suggestion)}
                  className="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center space-x-3 border-b border-gray-100 last:border-b-0"
                >
                  {/* Icon based on type */}
                  <div className="flex-shrink-0">
                    {suggestion.type === 'tag' && (
                      <div
                        className="w-4 h-4 rounded-full"
                        style={{ backgroundColor: suggestion.color }}
                      />
                    )}
                    {suggestion.type === 'user' && (
                      <img
                        src={suggestion.avatar_url}
                        alt=""
                        className="w-6 h-6 rounded-full"
                      />
                    )}
                    {(suggestion.type === 'image_title' || suggestion.type === 'album_title') && (
                      <svg className="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    )}
                  </div>
                  
                  <div className="flex-1 min-w-0">
                    <div className="text-sm font-medium text-gray-900 truncate">
                      {suggestion.text}
                    </div>
                    <div className="text-xs text-gray-500">
                      {suggestion.category}
                    </div>
                  </div>
                </button>
              ))}
            </motion.div>
          )}
        </AnimatePresence>
      </form>

      {/* Advanced Filters */}
      <AnimatePresence>
        {showFilters && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200"
          >
            <h3 className="text-sm font-medium text-gray-900 mb-3">Advanced Filters</h3>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {/* Date Range */}
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">Date From</label>
                <input
                  type="date"
                  value={filters.date_from}
                  onChange={(e) => setFilters({...filters, date_from: e.target.value})}
                  className="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
              
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">Date To</label>
                <input
                  type="date"
                  value={filters.date_to}
                  onChange={(e) => setFilters({...filters, date_to: e.target.value})}
                  className="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              {/* License */}
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">License</label>
                <select
                  value={filters.license}
                  onChange={(e) => setFilters({...filters, license: e.target.value})}
                  className="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                >
                  <option value="">Any License</option>
                  <option value="CC BY 4.0">CC BY 4.0</option>
                  <option value="CC BY-SA 4.0">CC BY-SA 4.0</option>
                  <option value="CC BY-NC 4.0">CC BY-NC 4.0</option>
                  <option value="All Rights Reserved">All Rights Reserved</option>
                </select>
              </div>

              {/* Camera */}
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">Camera</label>
                <input
                  type="text"
                  placeholder="e.g., Canon EOS R5"
                  value={filters.camera}
                  onChange={(e) => setFilters({...filters, camera: e.target.value})}
                  className="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                />
              </div>

              {/* Lens */}
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">Lens</label>
                <input
                  type="text"
                  placeholder="e.g., 24-70mm f/2.8"
                  value={filters.lens}
                  onChange={(e) => setFilters({...filters, lens: e.target.value})}
                  className="w-full text-sm border border-gray-300 rounded px-3 py-2 focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
            </div>

            {/* Checkboxes */}
            <div className="mt-4 flex items-center space-x-6">
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={filters.featured_only}
                  onChange={(e) => setFilters({...filters, featured_only: e.target.checked})}
                  className="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                />
                <span className="ml-2 text-sm text-gray-700">Featured only</span>
              </label>
              
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={filters.has_downloads}
                  onChange={(e) => setFilters({...filters, has_downloads: e.target.checked})}
                  className="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                />
                <span className="ml-2 text-sm text-gray-700">Downloadable</span>
              </label>
            </div>

            {/* Clear Filters */}
            <div className="mt-4 flex justify-end">
              <button
                type="button"
                onClick={() => setFilters({
                  album_id: '',
                  user_id: '',
                  tag_id: '',
                  license: '',
                  date_from: '',
                  date_to: '',
                  camera: '',
                  lens: '',
                  featured_only: false,
                  has_downloads: false,
                })}
                className="text-sm text-gray-600 hover:text-gray-800"
              >
                Clear filters
              </button>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}