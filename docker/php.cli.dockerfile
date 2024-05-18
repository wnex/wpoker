FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    intl \
    #exif \
    pcntl \
    bcmath \
    #gd \
    zip

WORKDIR /var/www/html