# Gallery Platform Setup Script for Windows
# This script automates the initial setup process

Write-Host "🎨 Gallery Platform Setup" -ForegroundColor Cyan
Write-Host "=========================" -ForegroundColor Cyan

# Check if .env exists
if (-Not (Test-Path ".env")) {
    Write-Host "📝 Creating .env file..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    Write-Host "✅ .env file created" -ForegroundColor Green
} else {
    Write-Host "✅ .env file already exists" -ForegroundColor Green
}

# Generate application key
Write-Host "🔑 Generating application key..." -ForegroundColor Yellow
php artisan key:generate

# Run database migrations
Write-Host "🗄️ Running database migrations..." -ForegroundColor Yellow
php artisan migrate --force

# Create storage link
Write-Host "🔗 Creating storage symlink..." -ForegroundColor Yellow
php artisan storage:link

# Create required directories
Write-Host "📁 Creating storage directories..." -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path "storage\app\public\images"
New-Item -ItemType Directory -Force -Path "storage\app\public\thumbnails"
New-Item -ItemType Directory -Force -Path "storage\logs"

# Seed database with sample data
Write-Host "🌱 Seeding database..." -ForegroundColor Yellow
php artisan db:seed

# Clear caches
Write-Host "🧹 Clearing caches..." -ForegroundColor Yellow
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Install and build frontend assets
Write-Host "🎨 Building frontend assets..." -ForegroundColor Yellow
npm install
npm run build

Write-Host ""
Write-Host "🎉 Setup completed successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "📍 Next steps:" -ForegroundColor Cyan
Write-Host "   1. Start the development server: php artisan serve"
Write-Host "   2. Start the queue worker: php artisan queue:work"
Write-Host "   3. Visit http://localhost:8000"
Write-Host ""
Write-Host "🐳 For Docker:" -ForegroundColor Cyan
Write-Host "   1. docker-compose up -d"
Write-Host "   2. docker-compose exec app php artisan setup:install"
Write-Host ""
Write-Host "👤 Default admin user:" -ForegroundColor Cyan
Write-Host "   Email: admin@gallery.local"
Write-Host "   Password: password"
Write-Host ""