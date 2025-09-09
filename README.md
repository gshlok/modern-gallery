# Gallery Platform

A modern, extensible media platform built with Laravel, Inertia.js, and React. This project revamps the classic PHP image gallery into a feature-rich, responsive, and modular platform while maintaining simplicity and ease of use.

## ✨ Features

### Phase 1 (Core Features)

- **User Authentication & Roles**: Admin, Editor, and Visitor roles with granular permissions
- **Image Upload & Processing**: Drag-and-drop batch upload with automatic thumbnail generation
- **Gallery & Albums**: Organized collections with responsive, accessible gallery views
- **Search & Filters**: Keyword search across titles, captions, tags, and metadata
- **Privacy Controls**: Per-image privacy settings (public, unlisted, private)
- **Metadata Management**: EXIF data extraction, custom tags, licensing info
- **Comments & Likes**: User engagement with spam protection
- **Analytics**: View counts and engagement tracking

### Phase 2 (Extended Features)

- **AI Image Generation**: Integration points for Stable Diffusion and external APIs
- **Vector Search**: Semantic search capabilities with embedding generation
- **Custom Themes**: Live palette editor with CSS variable system
- **Batch Operations**: Bulk image management and editing
- **Advanced Analytics**: Detailed usage statistics and insights

## 🛠 Tech Stack

- **Backend**: Laravel 10+ (PHP 8.2+)
- **Database**: PostgreSQL (recommended) or MySQL
- **Frontend**: Inertia.js + React + TypeScript
- **Styling**: Tailwind CSS with custom design system
- **Storage**: Local filesystem (dev) / S3/MinIO (production)
- **Cache & Queue**: Redis
- **Image Processing**: Intervention Image + ImageMagick
- **Deployment**: Docker + docker-compose

## 🚀 Quick Start

### Prerequisites

- Docker and Docker Compose
- Git

### One-Command Setup

```bash
# Clone the repository
git clone https://github.com/yourusername/gallery.git
cd gallery

# Copy environment file
cp .env.example .env

# Start the application with Docker
docker-compose up -d

# Wait for containers to start, then run setup
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan storage:link
docker-compose exec app npm install
docker-compose exec app npm run build

# Test the setup (optional)
./test-platform.sh  # Linux/macOS
# or
test-platform.bat   # Windows
```

The application will be available at http://localhost:8000

### Default Admin Account

- **Email**: admin@gallery.local
- **Password**: password

### Test User Accounts

- **Editor**: editor@gallery.local (password: password)
- **Visitor**: alex@example.com (password: password)
- **Visitor**: sarah@example.com (password: password)

### Manual Setup (Without Docker)

#### Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL or MySQL
- Redis

#### Installation Steps

1. **Clone and install dependencies**

```bash
git clone https://github.com/yourusername/gallery.git
cd gallery
composer install
npm install
```

2. **Environment configuration**

```bash
cp .env.example .env
php artisan key:generate
```

3. **Database setup**

```bash
# Create database
createdb gallery

# Configure .env file with your database credentials
# Then run migrations
php artisan migrate

# Seed with sample data
php artisan db:seed
```

4. **Storage setup**

```bash
php artisan storage:link
mkdir -p storage/app/public/images
mkdir -p storage/app/public/thumbnails
```

5. **Build frontend assets**

```bash
npm run build
# or for development
npm run dev
```

6. **Start services**

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work

# In another terminal, start scheduler
php artisan schedule:work
```

## 📁 Project Structure

```
gallery/
├── app/
│   ├── Http/Controllers/     # Web and API controllers
│   ├── Models/              # Eloquent models
│   ├── Services/            # Business logic services
│   ├── Jobs/                # Background jobs
│   └── Policies/            # Authorization policies
├── database/
│   ├── migrations/          # Database schema
│   ├── seeders/            # Sample data
│   └── factories/          # Model factories
├── resources/
│   ├── js/                 # React components and pages
│   │   ├── Components/     # Reusable UI components
│   │   ├── Pages/          # Inertia.js pages
│   │   └── Layouts/        # Page layouts
│   └── css/                # Stylesheets
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
├── docker/                 # Docker configuration
├── storage/                # File storage
└── public/                 # Public assets
```

## 🔧 Configuration

### Environment Variables

Key configuration options in `.env`:

```env
# Application
APP_NAME="Gallery Platform"
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=gallery
DB_USERNAME=gallery_user
DB_PASSWORD=gallery_password

# Media Settings
MEDIA_DISK=local
MEDIA_MAX_SIZE=10240          # KB
MEDIA_ALLOWED_MIMES=jpeg,jpg,png,gif,webp,svg
MEDIA_THUMBNAIL_SIZES=150,300,600,1200

# AI Generation (Optional)
AI_GENERATION_ENABLED=false
AI_GENERATION_PROVIDER=openai
AI_GENERATION_API_KEY=your_api_key

# Vector Search (Optional)
VECTOR_SEARCH_ENABLED=false
VECTOR_SEARCH_PROVIDER=openai
```

### User Roles & Permissions

- **Admin**: Full system access, user management, moderation
- **Editor**: Content management, upload permissions, basic moderation
- **Visitor**: View public content, comment, like

## 📖 API Documentation

### Core Endpoints

#### Images

```http
GET    /api/v1/images              # List images with pagination
POST   /api/v1/images              # Upload new image
GET    /api/v1/images/{slug}       # Get specific image
PATCH  /api/v1/images/{slug}       # Update image metadata
DELETE /api/v1/images/{slug}       # Delete image
POST   /api/v1/images/batch-upload # Batch upload multiple images
```

#### Albums

```http
GET    /api/v1/albums              # List albums
POST   /api/v1/albums              # Create album
GET    /api/v1/albums/{slug}       # Get specific album
PATCH  /api/v1/albums/{slug}       # Update album
DELETE /api/v1/albums/{slug}       # Delete album
```

#### Search

```http
GET    /api/v1/search?q={query}    # Search images and albums
GET    /api/v1/search/suggestions  # Get search suggestions
```

#### AI Generation (Placeholder)

```http
POST   /api/v1/ai/generate         # Generate image from prompt
GET    /api/v1/ai/generations      # List generation history
```

#### Vector Search (Placeholder)

```http
POST   /api/v1/vector/search       # Semantic search
POST   /api/v1/vector/similar/{id} # Find similar images
```

## 🎨 Frontend Components

### Key React Components

- **`ImageGallery`**: Responsive grid layout with lazy loading
- **`Lightbox`**: Full-screen image viewer with keyboard navigation
- **`ImageUpload`**: Drag-and-drop upload with progress tracking
- **`AlbumManager`**: Album creation and organization
- **`SearchBar`**: Real-time search with suggestions
- **`TagManager`**: Tag input with autocomplete
- **`ThemeCustomizer`**: Live theme editing interface

### Styling System

Built with Tailwind CSS and custom design tokens:

```css
/* Custom color palette */
:root {
  --color-primary: #3b82f6;
  --color-secondary: #6b7280;
  --color-accent: #f59e0b;
  /* ... */
}

/* Responsive grid system */
.gallery-grid {
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}
```

## 🔌 Extending the Platform

### Adding AI Providers

1. Create a new service class:

```php
<?php

namespace App\Services\AI;

class CustomAIProvider implements AIProviderInterface
{
    public function generateImage(string $prompt, array $options = []): array
    {
        // Implementation
    }
}
```

2. Register in `config/ai.php`:

```php
'providers' => [
    'custom' => CustomAIProvider::class,
],
```

### Adding Vector Search Providers

Similar pattern for vector search capabilities:

```php
<?php

namespace App\Services\VectorSearch;

class CustomVectorProvider implements VectorSearchInterface
{
    // Implementation
}
```

### Custom Themes

Create theme definitions:

```php
$theme = Theme::create([
    'name' => 'Dark Mode',
    'colors' => [
        'primary' => '#3b82f6',
        'background' => '#1f2937',
        // ...
    ],
]);
```

## 🧪 Testing the Application

### Automated Testing Script

We've provided automated testing scripts to validate your setup:

**Linux/macOS:**

```bash
# Make script executable
chmod +x test-platform.sh

# Run the test suite
./test-platform.sh
```

**Windows:**

```batch
# Run the test suite
test-platform.bat
```

This script will:

- ✅ Check Docker environment
- ✅ Verify all containers are running
- ✅ Test application health endpoints
- ✅ Validate API responses
- ✅ Check database connectivity
- ✅ Verify file structure
- ✅ Run basic performance tests

### Docker Environment Testing

1. **Start the application**:

   ```bash
   cd gallery
   docker-compose up -d
   ```

2. **Run the setup commands**:

   ```bash
   # Install dependencies and set up the application
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   docker-compose exec app php artisan storage:link
   docker-compose exec app npm install
   docker-compose exec app npm run build
   ```

3. **Access the application**:
   - Open http://localhost:8000 in your browser
   - Login with admin@gallery.local / password

### Manual Testing Scenarios

#### 1. Authentication & User Management

- **Login/Logout**: Test with different user roles
- **Registration**: Create new user accounts
- **Profile Management**: Update user profiles and avatars

#### 2. Image Upload & Management

- **Single Upload**: Upload individual images via web interface
- **Batch Upload**: Test drag-and-drop multiple image upload
- **Privacy Settings**: Test public, unlisted, and private visibility
- **Metadata**: Add titles, descriptions, alt text, and tags
- **EXIF Data**: Upload images with camera metadata

#### 3. Gallery & Albums

- **Gallery View**: Browse the responsive image gallery
- **Album Creation**: Create and organize albums
- **Album Management**: Add/remove images from albums
- **Lightbox**: Test full-screen image viewing with navigation

#### 4. Search & Filtering

- **Text Search**: Search by title, description, and tags
- **Filter Options**: Filter by album, user, date range
- **Advanced Search**: Test search suggestions and autocomplete

#### 5. Comments & Interactions

- **Commenting**: Add comments to images
- **Comment Replies**: Test threaded comment discussions
- **Likes**: Like/unlike images and comments
- **Moderation**: Test comment approval/rejection (as admin)

#### 6. AI Features (Placeholder Testing)

- **AI Generation**: Visit /ai-generation page
- **Mock Generation**: Test the AI generation interface with mock provider
- **Vector Search**: Visit /vector-search page
- **Semantic Search**: Test the vector search interface

### API Testing

#### Using curl or Postman

1. **Get authentication token**:

   ```bash
   curl -X POST http://localhost:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@gallery.local","password":"password"}'
   ```

2. **Test image upload**:

   ```bash
   curl -X POST http://localhost:8000/api/v1/images \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -F "image=@/path/to/your/image.jpg" \
     -F "title=Test Image" \
     -F "description=Uploaded via API"
   ```

3. **Test search API**:

   ```bash
   curl "http://localhost:8000/api/v1/search?q=nature&limit=10"
   ```

4. **Test AI generation (mock)**:
   ```bash
   curl -X POST http://localhost:8000/api/v1/ai/generate \
     -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"prompt":"A beautiful sunset over mountains","provider":"mock"}'
   ```

### Performance Testing

1. **Large File Upload**: Test with high-resolution images (10MB+)
2. **Concurrent Users**: Open multiple browser sessions
3. **Bulk Operations**: Upload 20+ images simultaneously
4. **Gallery Loading**: Test with 100+ images in gallery

### Error Handling Testing

1. **Invalid File Types**: Try uploading non-image files
2. **Large Files**: Test file size limits
3. **Network Issues**: Test with slow/interrupted connections
4. **Permission Errors**: Test unauthorized access attempts

### Browser Compatibility

- **Chrome/Edge**: Primary testing browsers
- **Firefox**: Secondary testing
- **Safari**: If available on macOS
- **Mobile**: Test responsive design on mobile devices

### Database Testing

```bash
# Connect to database (PostgreSQL)
docker-compose exec db psql -U gallery_user -d gallery

# Check sample data
\dt  # List tables
SELECT COUNT(*) FROM images;
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM albums;
```

### Log Monitoring

```bash
# Monitor application logs
docker-compose logs -f app

# Monitor web server logs
docker-compose logs -f nginx

# Monitor database logs
docker-compose logs -f db
```

### Troubleshooting Common Issues

1. **Port conflicts**: Change ports in docker-compose.yml if needed
2. **Permission issues**: Run `sudo chown -R $USER:$USER storage` if needed
3. **Database connection**: Ensure PostgreSQL container is running
4. **Asset compilation**: Run `npm run build` if styles are missing
5. **Cache issues**: Run `php artisan cache:clear` and `php artisan config:clear`

### Production-like Testing

1. **Enable production mode**:

   ```bash
   # Set in docker-compose.yml environment
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Test with optimized assets**:

   ```bash
   docker-compose exec app php artisan config:cache
   docker-compose exec app php artisan route:cache
   docker-compose exec app npm run build
   ```

3. **Load Testing** (optional):
   ```bash
   # Install Apache Bench
   ab -n 100 -c 10 http://localhost:8000/
   ```

## 📊 Performance & Scaling

### Optimization Features

- **Image Processing**: Asynchronous thumbnail generation
- **Caching**: Redis-backed caching for metadata and queries
- **CDN Ready**: S3/CloudFront integration for media delivery
- **Database Optimization**: Proper indexing and query optimization
- **Lazy Loading**: Frontend lazy loading for images

### Production Deployment

1. **Environment Setup**

```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Configure S3 for media storage
FILESYSTEM_DISK=s3
AWS_BUCKET=your-bucket
```

2. **Optimize for Production**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
npm run build
```

3. **Queue Workers**

```bash
# Set up supervisor for queue workers
supervisorctl start laravel-worker:*
```

## 🛡 Security

### Built-in Security Features

- **Authentication**: Laravel Sanctum for API authentication
- **Authorization**: Role-based permissions with Spatie Laravel Permission
- **File Upload Security**: MIME type validation, file size limits
- **CSRF Protection**: Built-in Laravel CSRF protection
- **XSS Prevention**: Input sanitization and output escaping
- **SQL Injection**: Eloquent ORM with parameter binding

### Security Headers

Configured in Nginx:

```nginx
add_header X-Frame-Options "SAMEORIGIN";
add_header X-XSS-Protection "1; mode=block";
add_header X-Content-Type-Options "nosniff";
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Use conventional commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel framework and community
- Inertia.js for seamless SPA development
- React and the frontend ecosystem
- Intervention Image for image processing
- All the amazing open-source contributors

## 📞 Support & Quick Help

### 🚀 Getting Started in 5 Minutes

1. **Clone and setup**:

   ```bash
   git clone https://github.com/yourusername/gallery.git
   cd gallery
   cp .env.example .env
   docker-compose up -d
   ```

2. **Initialize the application**:

   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   docker-compose exec app php artisan storage:link
   docker-compose exec app npm install
   docker-compose exec app npm run build
   ```

3. **Test and login**:
   - Run: `./test-platform.sh` (Linux/macOS) or `test-platform.bat` (Windows)
   - Visit: http://localhost:8000
   - Login: admin@gallery.local / password

### 🔧 Common Issues & Solutions

| Issue                     | Solution                                               |
| ------------------------- | ------------------------------------------------------ |
| Port 8000 already in use  | Change port in `docker-compose.yml` (ports: "8001:80") |
| Permission denied errors  | Run `sudo chown -R $USER:$USER storage`                |
| Database connection fails | Wait 30s for PostgreSQL to start, then retry           |
| npm build fails           | Delete `node_modules`, run `npm install` again         |
| Images not displaying     | Run `php artisan storage:link`                         |
| Queue jobs not processing | Check if queue container is running                    |

### 📚 Documentation

- **Documentation**: [Full documentation](https://docs.gallery.example.com)
- **Issues**: [GitHub Issues](https://github.com/yourusername/gallery/issues)
- **Discussions**: [GitHub Discussions](https://github.com/yourusername/gallery/discussions)
- **Email**: support@gallery.example.com

---

Built with ❤️ by the Gallery Platform team
