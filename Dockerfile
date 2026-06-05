FROM php:8.3-fpm
WORKDIR /var/www

# Added libzip-dev to apt-get, and zip to docker-php-ext-install
RUN apt-get update && apt-get install -y \
    git curl unzip zip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the Laravel source code into the image
COPY . .

# Added --no-scripts so Laravel doesn't crash trying to connect to a database that isn't there yet
RUN composer install --optimize-autoloader --no-dev --no-scripts

# Set permissions for Laravel storage and cache
# Create the directories first, then change their ownership
RUN mkdir -p /var/www/storage/framework/views \
             /var/www/storage/framework/cache \
             /var/www/storage/framework/sessions \
             /var/www/storage/logs \
             /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
CMD ["php-fpm"]