# ============================================
# Multi-stage Dockerfile for Symfony Backend
# ============================================

# Stage 1: Base PHP image with extensions
FROM php:8.3-fpm-alpine AS php_base

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl \
    bash \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    opcache \
    zip \
    bcmath \
    gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install APCu for caching
RUN pecl install apcu && docker-php-ext-enable apcu

# Configure PHP for production
RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "upload_max_filesize = 32M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "post_max_size = 32M" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "date.timezone = UTC" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "opcache.enable = 1" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "opcache.memory_consumption = 128" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "expose_php = Off" >> /usr/local/etc/php/conf.d/custom.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

# ============================================
# Stage 2: Development
# ============================================
FROM php_base AS development

# Install Xdebug for development
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Xdebug configuration
# Use 'trigger' mode to avoid performance issues - only activates when XDEBUG_TRIGGER is set
RUN echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Set environment
ENV APP_ENV=dev
ENV APP_DEBUG=1

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist

# Set permissions
RUN chown -R www-data:www-data /var/www/backend

USER www-data

EXPOSE 9000

CMD ["php-fpm"]

# ============================================
# Stage 3: Builder (for production)
# ============================================
FROM php_base AS builder

# Copy application files
COPY . .

# Install production dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative

# Clear caches and optimize
RUN php bin/console cache:clear --env=prod --no-debug \
    && php bin/console cache:warmup --env=prod --no-debug

# ============================================
# Stage 4: Production
# ============================================
FROM php_base AS production

# Production PHP configuration
ENV APP_ENV=prod
ENV APP_DEBUG=0

# Copy only necessary files from builder
COPY --from=builder /var/www/backend/vendor /var/www/backend/vendor
COPY --from=builder /var/www/backend/var/cache /var/www/backend/var/cache
COPY . .

# Create necessary directories
RUN mkdir -p var/cache var/log

# Security: Create non-root user
RUN addgroup -g 1000 appuser && adduser -D -u 1000 -G appuser appuser

# Set ownership
RUN chown -R appuser:appuser /var/www/backend

# Security: Run as non-root user
USER appuser

EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

CMD ["php-fpm"]
