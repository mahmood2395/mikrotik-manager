# ---------------------------------------------------------------
# Stage 1 — Install PHP dependencies
# We use a separate stage so composer and dev tools
# don't end up in the final production image
# ---------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

# Copy only composer files first for layer caching
# If composer.json hasn't changed, composer install is skipped on rebuild
COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs

# ---------------------------------------------------------------
# Stage 2 — The actual running image
# ---------------------------------------------------------------
FROM php:8.4-fpm

# Install system dependencies and PHP extensions Laravel needs
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql sockets \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copy vendor folder from the composer stage (not from your local machine)
COPY --from=vendor /app/vendor ./vendor

# Copy the rest of the application code
COPY . .

# Create directories in case they don't exist, then set permissions
RUN mkdir -p /var/www/storage/app/public \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/logs \
    /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

    # Fix PHP-FPM to listen on all interfaces
RUN echo '[www]\nlisten = 0.0.0.0:9000' > /usr/local/etc/php-fpm.d/zz-listen.conf
# Run as non-root user for security
USER www-data

EXPOSE 9000

CMD ["php-fpm"]