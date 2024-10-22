# Dockerfile

FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www

# Copy the existing application directory contents
COPY . .

# Install PHP dependencies using Composer
RUN composer install --no-autoloader --no-scripts

# Optionally run the autoloader
RUN composer dump-autoload --optimize

# Set the user
USER www-data

# Expose the port
EXPOSE 8000
