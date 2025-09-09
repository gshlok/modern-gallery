import React, { PropsWithChildren } from 'react';
import { Head } from '@inertiajs/react';

interface AppLayoutProps {
  title?: string;
  children: React.ReactNode;
}

export default function AppLayout({ title, children }: PropsWithChildren<AppLayoutProps>) {
  return (
    <div className="min-h-screen bg-gray-50">
      <Head title={title} />
      
      {/* Navigation */}
      <nav className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex items-center">
              <a href="/" className="flex items-center">
                <h1 className="text-xl font-bold text-gray-900">Gallery Platform</h1>
              </a>
              
              <div className="hidden md:flex ml-10 space-x-8">
                <a href="/gallery" className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                  Gallery
                </a>
                <a href="/albums" className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                  Albums
                </a>
                <a href="/ai-generation" className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                  AI Generate
                </a>
                <a href="/vector-search" className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                  Vector Search
                </a>
              </div>
            </div>

            <div className="flex items-center space-x-4">
              {/* Search Bar */}
              <div className="hidden md:block">
                <div className="relative">
                  <input
                    type="search"
                    placeholder="Search images..."
                    className="input w-64 pl-10"
                  />
                  <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                </div>
              </div>

              {/* User Menu */}
              <div className="flex items-center space-x-4">
                <a href="/login" className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                  Sign In
                </a>
                <a href="/register" className="btn">
                  Sign Up
                </a>
              </div>
            </div>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="flex-1">
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-white border-t border-gray-200 mt-20">
        <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
              <h3 className="text-sm font-semibold text-gray-400 tracking-wider uppercase">Gallery</h3>
              <ul className="mt-4 space-y-4">
                <li><a href="/gallery" className="text-base text-gray-500 hover:text-gray-900">Browse Images</a></li>
                <li><a href="/albums" className="text-base text-gray-500 hover:text-gray-900">Albums</a></li>
                <li><a href="/search" className="text-base text-gray-500 hover:text-gray-900">Search</a></li>
                <li><a href="/ai-generation" className="text-base text-gray-500 hover:text-gray-900">AI Generation</a></li>
                <li><a href="/vector-search" className="text-base text-gray-500 hover:text-gray-900">Vector Search</a></li>
              </ul>
            </div>
            <div>
              <h3 className="text-sm font-semibold text-gray-400 tracking-wider uppercase">Account</h3>
              <ul className="mt-4 space-y-4">
                <li><a href="/dashboard" className="text-base text-gray-500 hover:text-gray-900">Dashboard</a></li>
                <li><a href="/my-images" className="text-base text-gray-500 hover:text-gray-900">My Images</a></li>
                <li><a href="/my-albums" className="text-base text-gray-500 hover:text-gray-900">My Albums</a></li>
              </ul>
            </div>
            <div>
              <h3 className="text-sm font-semibold text-gray-400 tracking-wider uppercase">About</h3>
              <p className="mt-4 text-base text-gray-500">
                A modern, extensible media platform built with Laravel and React.
              </p>
            </div>
          </div>
          <div className="mt-8 border-t border-gray-200 pt-8">
            <p className="text-base text-gray-400 text-center">
              &copy; 2024 Gallery Platform. Built with ❤️ using Laravel and React.
            </p>
          </div>
        </div>
      </footer>
    </div>
  );
}