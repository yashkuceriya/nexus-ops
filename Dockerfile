# =============================================================================
# Stage 1: Composer dependencies
# =============================================================================
FROM composer:2 AS vendor

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

# =============================================================================
# Stage 2: Frontend assets
# =============================================================================
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./

RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# =============================================================================
# Stage 3: Production runtime
# =============================================================================
FROM php:8.3-fpm-alpine AS runtime

LABEL maintainer="facility-grid-bridge"

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    icu-libs \
    libzip \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    curl

# Install build dependencies and PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    linux-headers \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    opcache \
    pcntl \
    bcmath \
    intl \
    zip \
    gd \
    mbstring \
  && apk del .build-deps

# OPcache settings optimized for containers
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=64'; \
    echo 'opcache.max_accelerated_files=30000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.jit=1255'; \
    echo 'opcache.jit_buffer_size=128M'; \
  } > /usr/local/etc/php/conf.d/opcache.ini

# PHP-FPM pool configuration
RUN { \
    echo '[www]'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 20'; \
    echo 'pm.start_servers = 4'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 6'; \
    echo 'pm.max_requests = 1000'; \
    echo 'pm.status_path = /fpm-status'; \
    echo 'ping.path = /fpm-ping'; \
    echo 'catch_workers_output = yes'; \
    echo 'decorate_workers_output = no'; \
  } > /usr/local/etc/php-fpm.d/zz-pool.conf

# PHP production settings
RUN { \
    echo 'expose_php = Off'; \
    echo 'memory_limit = 256M'; \
    echo 'post_max_size = 64M'; \
    echo 'upload_max_filesize = 64M'; \
    echo 'max_execution_time = 60'; \
    echo 'realpath_cache_size = 4096K'; \
    echo 'realpath_cache_ttl = 600'; \
  } > /usr/local/etc/php/conf.d/php-production.ini

WORKDIR /var/www/html

# Copy nginx and supervisor configs
COPY deploy/nginx.conf /etc/nginx/http.d/default.conf
COPY deploy/supervisord.conf /etc/supervisord.conf

# Copy application code
COPY --chown=www-data:www-data . .

# Copy vendor dependencies from composer stage
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

# Copy built frontend assets from node stage
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# Remove dev/build files not needed in production
RUN rm -rf node_modules tests .env.example .git \
    docker-compose.yml Dockerfile \
    resources/js resources/css \
    storage/logs/*.log

# Ensure storage and cache directories exist with proper permissions
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R 775 storage bootstrap/cache

# Cache Laravel config, routes, and views
RUN php artisan config:cache \
  && php artisan route:cache \
  && php artisan view:cache \
  && php artisan event:cache

# Create nginx pid directory
RUN mkdir -p /run/nginx

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
