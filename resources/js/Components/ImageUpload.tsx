import React, { useState, useCallback, useRef } from 'react';
import { useForm } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';

interface Album {
  id: number;
  title: string;
  slug: string;
}

interface ImageUploadProps {
  albums?: Album[];
  onUploadComplete?: (images: any[]) => void;
  maxFiles?: number;
  maxFileSize?: number; // in MB
  allowedTypes?: string[];
}

interface UploadFile extends File {
  id: string;
  preview: string;
  progress: number;
  status: 'pending' | 'uploading' | 'success' | 'error';
  error?: string;
}

export default function ImageUpload({
  albums = [],
  onUploadComplete,
  maxFiles = 10,
  maxFileSize = 10,
  allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
}: ImageUploadProps) {
  const [files, setFiles] = useState<UploadFile[]>([]);
  const [isDragOver, setIsDragOver] = useState(false);
  const [uploading, setUploading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const { data, setData, post, processing, errors, reset } = useForm({
    album_id: '',
    visibility: 'public',
    allow_download: true,
    license: '',
    tags: '',
  });

  const processFiles = useCallback((fileList: FileList | File[]) => {
    const newFiles: UploadFile[] = [];
    
    Array.from(fileList).forEach((file) => {
      // Validate file type
      if (!allowedTypes.includes(file.type)) {
        alert(`File ${file.name} is not a supported image format.`);
        return;
      }

      // Validate file size
      if (file.size > maxFileSize * 1024 * 1024) {
        alert(`File ${file.name} is too large. Maximum size is ${maxFileSize}MB.`);
        return;
      }

      // Check if already added
      if (files.some(f => f.name === file.name && f.size === file.size)) {
        return;
      }

      const uploadFile: UploadFile = Object.assign(file, {
        id: Math.random().toString(36).substr(2, 9),
        preview: URL.createObjectURL(file),
        progress: 0,
        status: 'pending' as const,
      });

      newFiles.push(uploadFile);
    });

    if (files.length + newFiles.length > maxFiles) {
      alert(`Maximum ${maxFiles} files allowed.`);
      return;
    }

    setFiles(prev => [...prev, ...newFiles]);
  }, [files, maxFiles, maxFileSize, allowedTypes]);

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    setIsDragOver(false);
    
    const droppedFiles = e.dataTransfer.files;
    processFiles(droppedFiles);
  }, [processFiles]);

  const handleFileSelect = useCallback((e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFiles = e.target.files;
    if (selectedFiles) {
      processFiles(selectedFiles);
    }
  }, [processFiles]);

  const removeFile = useCallback((fileId: string) => {
    setFiles(prev => {
      const file = prev.find(f => f.id === fileId);
      if (file) {
        URL.revokeObjectURL(file.preview);
      }
      return prev.filter(f => f.id !== fileId);
    });
  }, []);

  const uploadFiles = async () => {
    if (files.length === 0) return;

    setUploading(true);
    const uploadPromises = files.map(async (file) => {
      if (file.status !== 'pending') return;

      setFiles(prev => prev.map(f => 
        f.id === file.id ? { ...f, status: 'uploading' } : f
      ));

      const formData = new FormData();
      formData.append('image', file);
      formData.append('title', file.name.replace(/\.[^/.]+$/, ''));
      formData.append('album_id', data.album_id);
      formData.append('visibility', data.visibility);
      formData.append('allow_download', data.allow_download ? '1' : '0');
      formData.append('license', data.license);
      formData.append('tags', data.tags);

      try {
        const response = await fetch('/api/v1/images', {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
        });

        if (response.ok) {
          const result = await response.json();
          setFiles(prev => prev.map(f => 
            f.id === file.id ? { ...f, status: 'success', progress: 100 } : f
          ));
          return result;
        } else {
          throw new Error('Upload failed');
        }
      } catch (error) {
        setFiles(prev => prev.map(f => 
          f.id === file.id ? { 
            ...f, 
            status: 'error', 
            error: error instanceof Error ? error.message : 'Upload failed' 
          } : f
        ));
        return null;
      }
    });

    const results = await Promise.all(uploadPromises);
    const successfulUploads = results.filter(Boolean);
    
    setUploading(false);
    
    if (successfulUploads.length > 0 && onUploadComplete) {
      onUploadComplete(successfulUploads);
    }

    // Clear successful uploads after a delay
    setTimeout(() => {
      setFiles(prev => prev.filter(f => f.status !== 'success'));
    }, 2000);
  };

  const clearAll = () => {
    files.forEach(file => URL.revokeObjectURL(file.preview));
    setFiles([]);
  };

  return (
    <div className="max-w-4xl mx-auto">
      {/* Upload Area */}
      <div
        className={`upload-area p-8 text-center ${isDragOver ? 'dragover' : ''}`}
        onDrop={handleDrop}
        onDragOver={(e) => { e.preventDefault(); setIsDragOver(true); }}
        onDragLeave={() => setIsDragOver(false)}
        onClick={() => fileInputRef.current?.click()}
      >
        <div className="flex flex-col items-center">
          <svg className="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
          <h3 className="text-lg font-medium text-gray-900 mb-2">
            Drop images here or click to browse
          </h3>
          <p className="text-gray-500 mb-4">
            Support for JPEG, PNG, GIF, WebP up to {maxFileSize}MB each
          </p>
          <p className="text-sm text-gray-400">
            Maximum {maxFiles} files • Drag and drop or batch select
          </p>
        </div>
        
        <input
          ref={fileInputRef}
          type="file"
          multiple
          accept={allowedTypes.join(',')}
          onChange={handleFileSelect}
          className="hidden"
        />
      </div>

      {/* Upload Options */}
      {files.length > 0 && (
        <div className="mt-6 p-6 bg-gray-50 rounded-lg">
          <h4 className="text-lg font-medium text-gray-900 mb-4">Upload Settings</h4>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Album (optional)
              </label>
              <select
                value={data.album_id}
                onChange={(e) => setData('album_id', e.target.value)}
                className="input w-full"
              >
                <option value="">No album</option>
                {albums.map((album) => (
                  <option key={album.id} value={album.id.toString()}>
                    {album.title}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Visibility
              </label>
              <select
                value={data.visibility}
                onChange={(e) => setData('visibility', e.target.value)}
                className="input w-full"
              >
                <option value="public">Public</option>
                <option value="unlisted">Unlisted</option>
                <option value="private">Private</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                License (optional)
              </label>
              <input
                type="text"
                value={data.license}
                onChange={(e) => setData('license', e.target.value)}
                placeholder="e.g., CC BY 4.0"
                className="input w-full"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Tags (comma separated)
              </label>
              <input
                type="text"
                value={data.tags}
                onChange={(e) => setData('tags', e.target.value)}
                placeholder="nature, landscape, sunset"
                className="input w-full"
              />
            </div>
          </div>

          <div className="mt-4">
            <label className="flex items-center">
              <input
                type="checkbox"
                checked={data.allow_download}
                onChange={(e) => setData('allow_download', e.target.checked)}
                className="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
              />
              <span className="ml-2 text-sm text-gray-700">Allow downloads</span>
            </label>
          </div>
        </div>
      )}

      {/* File List */}
      <AnimatePresence>
        {files.length > 0 && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: 'auto' }}
            exit={{ opacity: 0, height: 0 }}
            className="mt-6"
          >
            <div className="flex items-center justify-between mb-4">
              <h4 className="text-lg font-medium text-gray-900">
                Selected Files ({files.length})
              </h4>
              <div className="space-x-2">
                <button
                  onClick={clearAll}
                  className="btn btn-secondary"
                  disabled={uploading}
                >
                  Clear All
                </button>
                <button
                  onClick={uploadFiles}
                  disabled={uploading || files.every(f => f.status !== 'pending')}
                  className="btn"
                >
                  {uploading ? 'Uploading...' : 'Upload Images'}
                </button>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              {files.map((file) => (
                <motion.div
                  key={file.id}
                  initial={{ opacity: 0, scale: 0.9 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0, scale: 0.9 }}
                  className="relative bg-white rounded-lg border border-gray-200 overflow-hidden"
                >
                  <div className="aspect-video relative">
                    <img
                      src={file.preview}
                      alt={file.name}
                      className="w-full h-full object-cover"
                    />
                    
                    {/* Status Overlay */}
                    {file.status === 'uploading' && (
                      <div className="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                        <div className="text-white text-center">
                          <svg className="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                          </svg>
                          <div className="text-sm">Uploading...</div>
                        </div>
                      </div>
                    )}

                    {file.status === 'success' && (
                      <div className="absolute inset-0 bg-green-500 bg-opacity-50 flex items-center justify-center">
                        <svg className="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                    )}

                    {file.status === 'error' && (
                      <div className="absolute inset-0 bg-red-500 bg-opacity-50 flex items-center justify-center">
                        <svg className="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </div>
                    )}

                    {/* Remove Button */}
                    {file.status === 'pending' && (
                      <button
                        onClick={() => removeFile(file.id)}
                        className="absolute top-2 right-2 p-1 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-70"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </button>
                    )}
                  </div>

                  <div className="p-3">
                    <p className="text-sm font-medium text-gray-900 truncate">{file.name}</p>
                    <p className="text-xs text-gray-500">
                      {(file.size / 1024 / 1024).toFixed(2)} MB
                    </p>
                    {file.error && (
                      <p className="text-xs text-red-600 mt-1">{file.error}</p>
                    )}
                  </div>
                </motion.div>
              ))}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}