FROM php:8.3-fpm
WORKDIR /var/www

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y git curl unzip zip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the Laravel source code into the image
COPY . .

# Install production dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

CMD ["php-fpm"]