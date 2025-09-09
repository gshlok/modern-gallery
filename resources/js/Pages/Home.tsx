import React from 'react';
import { Head, Link } from '@inertiajs/react';
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

interface HomeProps {
  featuredImages: Image[];
  featuredAlbums: Album[];
  recentImages: Image[];
  stats: {
    totalImages: number;
    totalAlbums: number;
    totalUsers: number;
  };
}

export default function Home({ featuredImages, featuredAlbums, recentImages, stats }: HomeProps) {
  return (
    <AppLayout title="Welcome">
      {/* Hero Section */}
      <div className="relative bg-gradient-to-r from-primary-600 to-primary-800 overflow-hidden">
        <div className="absolute inset-0">
          <div className="absolute inset-0 bg-gradient-to-r from-primary-600 to-primary-800 mix-blend-multiply" />
        </div>
        
        <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
          <div className="text-center">
            <h1 className="text-4xl md:text-6xl font-bold text-white mb-6">
              Share Your Vision
            </h1>
            <p className="text-xl md:text-2xl text-primary-100 mb-12 max-w-3xl mx-auto">
              A modern platform for photographers and artists to showcase their work, 
              discover inspiration, and connect with a creative community.
            </p>
            
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Link href="/gallery" className="btn bg-white text-primary-600 hover:bg-gray-50">
                Explore Gallery
              </Link>
              <Link href="/register" className="btn border-2 border-white text-white hover:bg-white hover:text-primary-600">
                Join Community
              </Link>
            </div>

            {/* Stats */}
            <div className="mt-16 grid grid-cols-3 gap-8 max-w-md mx-auto">
              <div className="text-center">
                <div className="text-3xl font-bold text-white">{stats.totalImages.toLocaleString()}</div>
                <div className="text-primary-100">Images</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-white">{stats.totalAlbums.toLocaleString()}</div>
                <div className="text-primary-100">Albums</div>
              </div>
              <div className="text-center">
                <div className="text-3xl font-bold text-white">{stats.totalUsers.toLocaleString()}</div>
                <div className="text-primary-100">Creators</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        {/* Featured Images */}
        {featuredImages.length > 0 && (
          <section className="mb-20">
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold text-gray-900 mb-4">Featured Images</h2>
              <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                Discover the most outstanding work from our creative community
              </p>
            </div>
            
            <ImageGallery images={featuredImages} />
            
            <div className="text-center mt-8">
              <Link href="/gallery" className="btn">
                View All Images
              </Link>
            </div>
          </section>
        )}

        {/* Featured Albums */}
        {featuredAlbums.length > 0 && (
          <section className="mb-20">
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold text-gray-900 mb-4">Featured Albums</h2>
              <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                Curated collections that tell compelling visual stories
              </p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
              {featuredAlbums.map((album) => (
                <Link
                  key={album.id}
                  href={`/albums/${album.slug}`}
                  className="group block"
                >
                  <div className="card overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div className="aspect-video relative overflow-hidden">
                      <img
                        src={album.cover_image_url}
                        alt={album.title}
                        className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                      />
                      <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-60" />
                      <div className="absolute bottom-0 left-0 right-0 p-6">
                        <h3 className="text-xl font-bold text-white mb-2">{album.title}</h3>
                        <div className="flex items-center justify-between text-white text-sm">
                          <span>by {album.user.name}</span>
                          <span>{album.image_count} images</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
            
            <div className="text-center mt-8">
              <Link href="/albums" className="btn btn-secondary">
                Browse All Albums
              </Link>
            </div>
          </section>
        )}

        {/* Features Section */}
        <section className="mb-20">
          <div className="text-center mb-12">
            <h2 className="text-3xl font-bold text-gray-900 mb-4">Why Choose Our Platform?</h2>
            <p className="text-lg text-gray-600 max-w-2xl mx-auto">
              Built for photographers and artists with features that matter
            </p>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="text-center">
              <div className="bg-primary-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">Easy Upload</h3>
              <p className="text-gray-600">
                Drag-and-drop batch upload with automatic thumbnail generation and metadata extraction
              </p>
            </div>
            
            <div className="text-center">
              <div className="bg-primary-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-2">Smart Discovery</h3>
              <p className="text-gray-600">
                Advanced search with keyword filtering across titles, tags, and metadata
              </p>
            </div>
            
            <div className="text-center">
              <div className="bg-primary-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg className="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h3 className="text-xl font-semibold text-gray-900 mb-3">Community Driven</h3>
              <p className="text-gray-600">
                Connect with fellow creators through comments, likes, and collaborative albums
              </p>
            </div>
          </div>
        </section>

        {/* Recent Images */}
        {recentImages.length > 0 && (
          <section className="mb-20">
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold text-gray-900 mb-4">Latest Uploads</h2>
              <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                Fresh content from our community of creators
              </p>
            </div>
            
            <ImageGallery images={recentImages} />
            
            <div className="text-center mt-8">
              <Link href="/gallery" className="btn">
                Explore More
              </Link>
            </div>
          </section>
        )}

        {/* CTA Section */}
        <section className="bg-gradient-to-r from-primary-600 to-primary-800 rounded-2xl p-12 text-center text-white">
          <h2 className="text-3xl font-bold mb-4">Ready to Share Your Art?</h2>
          <p className="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
            Join thousands of photographers and artists who trust our platform to showcase their work
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link href="/register" className="btn bg-white text-primary-600 hover:bg-gray-50">
              Create Account
            </Link>
            <Link href="/gallery" className="btn border-2 border-white text-white hover:bg-white hover:text-primary-600">
              Browse Gallery
            </Link>
          </div>
        </section>
      </div>
    </AppLayout>
  );
}