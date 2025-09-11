

```markdown
# Laravel Image Gallery

A feature-rich image gallery application built with Laravel 12, supporting image uploads, albums, user authentication, and more.

---

## Requirements

- PHP 8.1 or higher
- Composer
- SQLite (default) or MySQL/PostgreSQL
- Node.js & npm (optional, if frontend build steps are needed)

---

## Installation & Setup

1. **Clone the repository**

```
git clone https://github.com/yourusername/your-repo.git
cd your-repo
```

2. **Install PHP dependencies**

```
composer install
```

3. **Copy the example environment file**

```
cp .env.example .env
```

4. **Generate application key**

```
php artisan key:generate
```

5. **Configure database**

- By default, SQLite is used. Create the database file:

```
touch database/database.sqlite
```

- Or configure MySQL/PostgreSQL in `.env` file by setting:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. **Run database migrations**

```
php artisan migrate
```

7. **Create symbolic link for storage**

```
php artisan storage:link
```

8. **Start the development server**

```
php artisan serve
```

9. **Open in browser**

Navigate to [http://localhost:8000](http://localhost:8000) to access the app.

---

## Usage

- Register a user account to upload images.
- Create albums to organize images.
- Upload multiple images with live preview.
- Rename, delete, and move images between albums.
- Manage your account, including deletion.
- Explore AI image generation features (if enabled).

---

## Notes

- Uploaded images and thumbnails are stored in `storage/app/public/images` and `storage/app/public/thumbnails`.
- Ensure `storage/` and `bootstrap/cache/` directories are writable.
- To clear caches:

```
php artisan optimize:clear
```

---

## Security Tips

- Never commit `.env` with real credentials.
- Use environment variables for sensitive settings.

---

## Troubleshooting

- If you see route errors or blank pages, clear caches:

```
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

- Review logs in `storage/logs/laravel.log` for detailed error information.

---

## License

This project is licensed under the MIT License.

---
```