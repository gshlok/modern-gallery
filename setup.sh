#!/bin/bash

# Gallery Platform Setup Script
# This script automates the initial setup process

echo "🎨 Gallery Platform Setup"
echo "========================="

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Create storage link
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Create required directories
echo "📁 Creating storage directories..."
mkdir -p storage/app/public/images
mkdir -p storage/app/public/thumbnails
mkdir -p storage/logs

# Set permissions
echo "🔐 Setting storage permissions..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Seed database with sample data
echo "🌱 Seeding database..."
php artisan db:seed

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Install and build frontend assets
echo "🎨 Building frontend assets..."
npm install
npm run build

echo ""
echo "🎉 Setup completed successfully!"
echo ""
echo "📍 Next steps:"
echo "   1. Start the development server: php artisan serve"
echo "   2. Start the queue worker: php artisan queue:work"
echo "   3. Visit http://localhost:8000"
echo ""
echo "🐳 For Docker:"
echo "   1. docker-compose up -d"
echo "   2. docker-compose exec app php artisan setup:install"
echo ""
echo "👤 Default admin user:"
echo "   Email: admin@gallery.local"
echo "   Password: password"
echo ""