import React, { useState, useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

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

interface ImageGalleryProps {
  images: Image[];
  loading?: boolean;
  onLoadMore?: () => void;
  hasMore?: boolean;
}

interface LightboxProps {
  image: Image;
  images: Image[];
  currentIndex: number;
  onClose: () => void;
  onPrevious: () => void;
  onNext: () => void;
}

// Lightbox Component
function Lightbox({ image, images, currentIndex, onClose, onPrevious, onNext }: LightboxProps) {
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      switch (e.key) {
        case 'Escape':
          onClose();
          break;
        case 'ArrowLeft':
          onPrevious();
          break;
        case 'ArrowRight':
          onNext();
          break;
      }
    };

    document.addEventListener('keydown', handleKeyDown);
    document.body.style.overflow = 'hidden';

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      document.body.style.overflow = 'unset';
    };
  }, [onClose, onPrevious, onNext]);

  return (
    <AnimatePresence>
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        className="lightbox-backdrop z-50"
        onClick={onClose}
      >
        <div className="relative w-full h-full flex items-center justify-center p-4">
          {/* Close Button */}
          <button
            onClick={onClose}
            className="absolute top-4 right-4 z-60 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-all"
          >
            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          {/* Navigation Buttons */}
          {currentIndex > 0 && (
            <button
              onClick={(e) => { e.stopPropagation(); onPrevious(); }}
              className="absolute left-4 top-1/2 transform -translate-y-1/2 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-all"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
          )}

          {currentIndex < images.length - 1 && (
            <button
              onClick={(e) => { e.stopPropagation(); onNext(); }}
              className="absolute right-4 top-1/2 transform -translate-y-1/2 p-2 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70 transition-all"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          )}

          {/* Image */}
          <motion.img
            key={image.id}
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.9 }}
            src={image.url}
            alt={image.alt_text || image.title}
            className="lightbox-image"
            onClick={(e) => e.stopPropagation()}
          />

          {/* Image Info */}
          <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-6">
            <div className="text-white">
              <h3 className="text-xl font-semibold mb-2">{image.title}</h3>
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-4">
                  <div className="flex items-center space-x-2">
                    <img
                      src={image.user.avatar_url}
                      alt={image.user.name}
                      className="w-8 h-8 rounded-full"
                    />
                    <span className="text-sm">{image.user.name}</span>
                  </div>
                  <div className="flex items-center space-x-4 text-sm">
                    <span>{image.view_count} views</span>
                    <span>{image.like_count} likes</span>
                  </div>
                </div>
                <div className="text-sm">
                  {currentIndex + 1} of {images.length}
                </div>
              </div>
            </div>
          </div>
        </div>
      </motion.div>
    </AnimatePresence>
  );
}

// Image Card Component
function ImageCard({ image, onClick }: { image: Image; onClick: () => void }) {
  const [imageLoaded, setImageLoaded] = useState(false);
  const [liked, setLiked] = useState(image.is_liked);
  const [likeCount, setLikeCount] = useState(image.like_count);

  const handleLike = async (e: React.MouseEvent) => {
    e.stopPropagation();
    
    try {
      const response = await fetch(`/api/v1/images/${image.slug}/like`, {
        method: liked ? 'DELETE' : 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });

      if (response.ok) {
        setLiked(!liked);
        setLikeCount(prev => liked ? prev - 1 : prev + 1);
      }
    } catch (error) {
      console.error('Failed to toggle like:', error);
    }
  };

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      className="image-grid-item cursor-pointer group"
      onClick={onClick}
    >
      {/* Image */}
      <div className="relative overflow-hidden bg-gray-200" style={{ aspectRatio: image.aspect_ratio }}>
        {!imageLoaded && (
          <div className="loading-skeleton absolute inset-0" />
        )}
        <img
          src={image.thumbnail_url}
          alt={image.title}
          className={`w-full h-full object-cover transition-transform duration-300 group-hover:scale-105 ${
            imageLoaded ? 'opacity-100' : 'opacity-0'
          }`}
          onLoad={() => setImageLoaded(true)}
          loading="lazy"
        />
        
        {/* Overlay */}
        <div className="image-overlay">
          <div className="absolute inset-0 p-4 flex flex-col justify-between">
            {/* Tags */}
            <div className="flex flex-wrap gap-1">
              {image.tags.slice(0, 3).map((tag) => (
                <span
                  key={tag.id}
                  className="badge text-xs px-2 py-1 rounded-full text-white"
                  style={{ backgroundColor: tag.color }}
                >
                  {tag.name}
                </span>
              ))}
              {image.tags.length > 3 && (
                <span className="badge badge-secondary text-xs px-2 py-1 rounded-full">
                  +{image.tags.length - 3}
                </span>
              )}
            </div>

            {/* Bottom Info */}
            <div>
              <h3 className="text-white font-semibold text-sm mb-2 line-clamp-2">{image.title}</h3>
              <div className="flex items-center justify-between text-white text-xs">
                <div className="flex items-center space-x-2">
                  <img
                    src={image.user.avatar_url}
                    alt={image.user.name}
                    className="w-5 h-5 rounded-full"
                  />
                  <span>{image.user.name}</span>
                </div>
                <div className="flex items-center space-x-3">
                  <span>{image.view_count}</span>
                  <button
                    onClick={handleLike}
                    className={`flex items-center space-x-1 hover:scale-110 transition-transform ${
                      liked ? 'text-red-400' : 'text-white'
                    }`}
                  >
                    <svg className="w-4 h-4" fill={liked ? 'currentColor' : 'none'} stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span>{likeCount}</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </motion.div>
  );
}

// Main Gallery Component
export default function ImageGallery({ images, loading = false, onLoadMore, hasMore = false }: ImageGalleryProps) {
  const [lightboxImage, setLightboxImage] = useState<Image | null>(null);
  const [lightboxIndex, setLightboxIndex] = useState(0);

  const openLightbox = useCallback((image: Image) => {
    const index = images.findIndex(img => img.id === image.id);
    setLightboxImage(image);
    setLightboxIndex(index);
  }, [images]);

  const closeLightbox = useCallback(() => {
    setLightboxImage(null);
  }, []);

  const previousImage = useCallback(() => {
    if (lightboxIndex > 0) {
      const newIndex = lightboxIndex - 1;
      setLightboxIndex(newIndex);
      setLightboxImage(images[newIndex]);
    }
  }, [lightboxIndex, images]);

  const nextImage = useCallback(() => {
    if (lightboxIndex < images.length - 1) {
      const newIndex = lightboxIndex + 1;
      setLightboxIndex(newIndex);
      setLightboxImage(images[newIndex]);
    }
  }, [lightboxIndex, images]);

  // Infinite scroll
  useEffect(() => {
    if (!onLoadMore || !hasMore) return;

    const handleScroll = () => {
      if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
        onLoadMore();
      }
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, [onLoadMore, hasMore]);

  if (images.length === 0 && !loading) {
    return (
      <div className="text-center py-20">
        <svg className="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 className="mt-4 text-lg font-medium text-gray-900">No images found</h3>
        <p className="mt-2 text-gray-500">Get started by uploading your first image.</p>
      </div>
    );
  }

  return (
    <>
      <div className="gallery-grid">
        {images.map((image) => (
          <ImageCard
            key={image.id}
            image={image}
            onClick={() => openLightbox(image)}
          />
        ))}
        
        {/* Loading skeleton cards */}
        {loading && Array.from({ length: 8 }).map((_, index) => (
          <div key={`skeleton-${index}`} className="image-grid-item">
            <div className="loading-skeleton aspect-photo rounded-lg" />
          </div>
        ))}
      </div>

      {/* Load more button */}
      {hasMore && !loading && (
        <div className="mt-12 text-center">
          <button
            onClick={onLoadMore}
            className="btn"
          >
            Load More Images
          </button>
        </div>
      )}

      {/* Lightbox */}
      {lightboxImage && (
        <Lightbox
          image={lightboxImage}
          images={images}
          currentIndex={lightboxIndex}
          onClose={closeLightbox}
          onPrevious={previousImage}
          onNext={nextImage}
        />
      )}
    </>
  );
}