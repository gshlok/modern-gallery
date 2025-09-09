import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import ImageGallery from '@/Components/ImageGallery';

interface Image {
  id: number;
  title: string;
  slug: string;
  url: string;
  thumbnail_url: string;
  large_thumbnail_url: string;
  width: number;
  height: number;
  aspect_ratio: number;
  view_count: number;
  like_count: number;
  is_liked: boolean;
  user: {
    id: number;
    name: string;
    avatar_url: string;
  };
  tags: Array<{
    id: number;
    name: string;
    color: string;
  }>;
  created_at: string;
}

interface Album {
  id: number;
  title: string;
  slug: string;
  cover_image_url: string;
  image_count: number;
  user: {
    name: string;
  };
}

interface GalleryPageProps {
  images: {
    data: Image[];
    links: any;
    meta: any;
  };
  featuredAlbums: Album[];
  filters: {
    search?: string;
    tag?: string;
    user?: string;
  };
  stats: {
    totalImages: number;
    totalAlbums: number;
    totalUsers: number;
  };
}

export default function GalleryPage({ images, featuredAlbums, filters, stats }: GalleryPageProps) {
  const [searchQuery, setSearchQuery] = useState(filters.search || '');
  const [loading, setLoading] = useState(false);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    router.get('/gallery', { search: searchQuery }, {
      preserveState: true,
      preserveScroll: true,
    });
  };

  const loadMore = () => {
    if (images.links.next && !loading) {
      setLoading(true);
      router.get(images.links.next, {}, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => setLoading(false),
      });
    }
  };

  return (
    <AppLayout title="Gallery">
      {/* Hero Section */}
      <div className="bg-gradient-to-r from-primary-600 to-primary-800 text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <div className="text-center">
            <h1 className="text-4xl font-bold mb-4">
              Discover Amazing Images
            </h1>
            <p className="text-xl text-primary-100 mb-8">
              Explore our community's creative photography and artwork
            </p>
            
            {/* Search Bar */}
            <form onSubmit={handleSearch} className="max-w-lg mx-auto">
              <div className="relative">
                <input
                  type="search"
                  placeholder="Search images, tags, or photographers..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full px-4 py-3 pl-12 rounded-lg border-0 text-gray-900 focus:ring-2 focus:ring-white focus:ring-opacity-50"
                />
                <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <button
                  type="submit"
                  className="absolute inset-y-0 right-0 pr-4 flex items-center"
                >
                  <span className="bg-primary-600 text-white px-4 py-2 rounded-md hover:bg-primary-700">
                    Search
                  </span>
                </button>
              </div>
            </form>

            {/* Stats */}
            <div className="mt-12 grid grid-cols-3 gap-8 max-w-md mx-auto">
              <div className="text-center">
                <div className="text-2xl font-bold">{stats.totalImages.toLocaleString()}</div>
                <div className="text-primary-100">Images</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold">{stats.totalAlbums.toLocaleString()}</div>
                <div className="text-primary-100">Albums</div>
              </div>
              <div className="text-center">
                <div className="text-2xl font-bold">{stats.totalUsers.toLocaleString()}</div>
                <div className="text-primary-100">Creators</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {/* Featured Albums */}
        {featuredAlbums.length > 0 && (
          <section className="mb-16">
            <div className="flex items-center justify-between mb-8">
              <h2 className="text-2xl font-bold text-gray-900">Featured Albums</h2>
              <Link href="/albums" className="text-primary-600 hover:text-primary-500 font-medium">
                View all albums →
              </Link>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {featuredAlbums.map((album) => (
                <Link
                  key={album.id}
                  href={`/albums/${album.slug}`}
                  className="group block"
                >
                  <div className="card overflow-hidden hover:shadow-lg transition-shadow">
                    <div className="aspect-video relative overflow-hidden">
                      <img
                        src={album.cover_image_url}
                        alt={album.title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                      <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all" />
                    </div>
                    <div className="p-4">
                      <h3 className="font-semibold text-gray-900 group-hover:text-primary-600 transition-colors">
                        {album.title}
                      </h3>
                      <div className="flex items-center justify-between mt-2 text-sm text-gray-500">
                        <span>by {album.user.name}</span>
                        <span>{album.image_count} images</span>
                      </div>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </section>
        )}

        {/* Filters */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h2 className="text-2xl font-bold text-gray-900">
              {filters.search ? `Search results for "${filters.search}"` : 'Latest Images'}
            </h2>
            <p className="text-gray-600 mt-1">
              {images.meta.total.toLocaleString()} images found
            </p>
          </div>

          <div className="flex items-center space-x-4">
            {/* Filter buttons */}
            <div className="flex space-x-2">
              <button className="px-4 py-2 bg-primary-100 text-primary-700 rounded-lg hover:bg-primary-200 transition-colors">
                Recent
              </button>
              <button className="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                Popular
              </button>
              <button className="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                Trending
              </button>
            </div>

            {/* View toggle */}
            <div className="flex border border-gray-300 rounded-lg overflow-hidden">
              <button className="p-2 bg-primary-600 text-white">
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
              </button>
              <button className="p-2 text-gray-700 hover:bg-gray-50">
                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clipRule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        {/* Clear filters */}
        {(filters.search || filters.tag || filters.user) && (
          <div className="mb-6 flex items-center space-x-2">
            <span className="text-sm text-gray-500">Active filters:</span>
            {filters.search && (
              <span className="inline-flex items-center px-3 py-1 rounded-full text-sm bg-primary-100 text-primary-800">
                Search: {filters.search}
                <button className="ml-2 text-primary-600 hover:text-primary-800">
                  <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                  </svg>
                </button>
              </span>
            )}
            <Link
              href="/gallery"
              className="text-sm text-primary-600 hover:text-primary-500"
            >
              Clear all
            </Link>
          </div>
        )}

        {/* Image Gallery */}
        <ImageGallery
          images={images.data}
          loading={loading}
          onLoadMore={images.links.next ? loadMore : undefined}
          hasMore={!!images.links.next}
        />

        {/* Empty State */}
        {images.data.length === 0 && (
          <div className="text-center py-20">
            <svg className="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 className="mt-4 text-lg font-medium text-gray-900">No images found</h3>
            <p className="mt-2 text-gray-500">
              {filters.search 
                ? `No images match your search for "${filters.search}"`
                : "No images have been uploaded yet."
              }
            </p>
            {!filters.search && (
              <div className="mt-6">
                <Link href="/images/create" className="btn">
                  Upload First Image
                </Link>
              </div>
            )}
          </div>
        )}
      </div>
    </AppLayout>
  );
}