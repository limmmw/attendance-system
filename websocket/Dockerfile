# Base image PHP 8.3 CLI
FROM php:8.3-cli

# Install dependensi OS
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    zlib1g-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy server code
COPY ./server.php /var/www/html/server.php

# Copy composer.json jika ada dependency tambahan
COPY ./composer.json /var/www/html/composer.json

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port WebSocket
EXPOSE 8081

# Jalankan WebSocket server
CMD ["php", "/var/www/html/server.php"]
