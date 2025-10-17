# Gunakan PHP 8.3 FPM official image
FROM php:8.3-fpm

# Install dependensi OS dan ekstensi PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer (dari official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy aplikasi ke container
COPY ./app /var/www/html

# Install dependencies PHP menggunakan Composer
RUN composer install --no-dev --optimize-autoloader

# Set permission
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port FPM default (9000) untuk diakses Nginx container
EXPOSE 900

# Jalankan PHP-FPM
CMD ["php-fpm"]
