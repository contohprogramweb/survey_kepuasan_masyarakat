# =============================================================================
# Docker Configuration untuk Aplikasi IKM v2.0.0
# =============================================================================

FROM php:8.2-fpm-alpine

# Label metadata
LABEL maintainer="IKM Development Team"
LABEL version="2.0.0"
LABEL description="Aplikasi Survei Kepuasan Masyarakat (IKM)"

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    redis \
    bash \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    openldap-dev \
    imagemagick \
    imagemagick-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        opcache \
        zip \
        soap \
        ldap \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/writable \
    && chmod -R 777 /var/www/html/writable/cache \
    && chmod -R 777 /var/www/html/writable/logs \
    && chmod -R 777 /var/www/html/writable/session \
    && chmod -R 777 /var/www/html/writable/uploads

# Copy Nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Copy PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy Supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories
RUN mkdir -p /var/log/nginx \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /run/nginx \
    && mkdir -p /var/lib/redis

# Expose ports
EXPOSE 80 443 9090

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Entry point script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Start services with Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
