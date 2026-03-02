# syntax=docker/dockerfile:1

# ============================================
# Stage 1: Build dependencies
# ============================================
FROM composer:2 AS composer-deps

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev

# ============================================
# Stage 2: Build frontend assets
# ============================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci --no-audit

COPY . .
RUN npm run build

# ============================================
# Stage 3: Production image with FrankenPHP
# ============================================
FROM dunglas/frankenphp:1-php8.4-alpine AS production

# Install PHP extensions required by Laravel + PostgreSQL
RUN install-php-extensions \
    bcmath \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_pgsql \
    pgsql \
    redis \
    zip

# Set working directory
WORKDIR /app

# Copy application from build stages
COPY --from=composer-deps /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
COPY . .

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/99-ekklesia.ini"

# Environment
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

# Expose Octane port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:8000/health || exit 1

# Run Laravel Octane with FrankenPHP
ENTRYPOINT ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]
