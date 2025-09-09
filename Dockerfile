FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    imagemagick-dev \
    imagemagick \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql gd xml zip pcntl

# Install ImageMagick PHP extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del .build-deps

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Create storage directories
RUN mkdir -p storage/app/public/images \
    && mkdir -p storage/app/public/thumbnails \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]