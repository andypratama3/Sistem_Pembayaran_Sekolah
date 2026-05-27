# Stage 1: Composer (PHP dependencies)
FROM composer:latest AS composer
WORKDIR /build
COPY src/composer.json src/composer.lock ./
RUN composer install --ignore-platform-reqs \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction
COPY src/ .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# Stage 2: Node frontend
FROM node:20-alpine AS frontend
WORKDIR /build
COPY src/package.json src/package-lock.json ./
RUN npm ci --production=false
COPY src/ .
RUN npm run build

# Stage 3: Final PHP image
FROM php:8.3-fpm-alpine

LABEL org.opencontainers.image.title="ProductSchool"
LABEL org.opencontainers.image.description="Laravel 12 School Management System"
LABEL org.opencontainers.image.vendor="ProductSchool"
LABEL org.opencontainers.image.version="1.0"
LABEL maintainer="info@productschool.com"

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    openssl-dev \
    libpq-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    linux-headers \
    autoconf \
    automake \
    make \
    gcc \
    g++ \
    supervisor \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    sockets \
    intl \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && apk del autoconf automake make gcc g++ \
    && rm -rf /tmp/* /var/cache/apk/*

# Install Composer (optional, untuk runtime jika perlu)
COPY --from=composer /usr/bin/composer /usr/bin/composer

# PHP production settings
RUN { \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.revalidate_freq=2"; \
    echo "opcache.fast_shutdown=1"; \
    echo "opcache.enable_cli=1"; \
    echo "opcache.validate_timestamps=0"; \
    echo "realpath_cache_size=4096K"; \
    echo "realpath_cache_ttl=600"; \
} > /usr/local/etc/php/conf.d/opcache.ini

RUN { \
    echo "upload_max_filesize=100M"; \
    echo "post_max_size=100M"; \
    echo "max_execution_time=300"; \
    echo "memory_limit=512M"; \
    echo "date.timezone=Asia/Jakarta"; \
    echo "max_input_vars=3000"; \
    echo "session.gc_probability=1"; \
    echo "session.gc_divisor=1000"; \
    echo "session.gc_maxlifetime=1440"; \
} > /usr/local/etc/php/conf.d/app.ini

# Install wait-for-it script untuk healthcheck
RUN curl -o /usr/local/bin/wait-for-it.sh https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh \
    && chmod +x /usr/local/bin/wait-for-it.sh

WORKDIR /app

# Copy dependencies & frontend build
COPY --from=composer /build/vendor ./vendor
COPY --from=frontend /build/public/build ./public/build
COPY src/ .

# Copy entrypoint script
COPY docker/entrypoint.sh /app/docker-entrypoint.sh

# Buat folder storage & cache dengan permission yang benar
RUN mkdir -p \
    storage/framework/{sessions,views,cache,testing} \
    storage/logs \
    bootstrap/cache \
    public/uploads \
    public/storage \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache public

# Pastikan entrypoint executable
RUN chmod +x /app/docker-entrypoint.sh

# Switch ke user www-data untuk security
USER www-data

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=10s --start-period=90s --retries=5 \
    CMD php artisan health:check 2>/dev/null || php-fpm -t > /dev/null 2>&1 || exit 1

ENTRYPOINT ["/app/docker-entrypoint.sh"]
CMD ["php-fpm"]
