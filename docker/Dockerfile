# =============================================================================
# Docker Configuration untuk Aplikasi IKM v2.0.0
# Environment: Development
# =============================================================================

FROM php:8.2-fpm-alpine

# Label metadata
LABEL maintainer="IKM Development Team"
LABEL version="2.0.0"
LABEL description="Aplikasi Survei Kepuasan Masyarakat (IKM) - Development Environment"

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
    imagemagick-dev \
    linux-headers \
    make \
    g++

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

# Install Xdebug untuk development
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=develop,debug,profile" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/var/log/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create necessary directories
RUN mkdir -p /var/log/nginx \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /var/log/xdebug \
    && mkdir -p /run/nginx \
    && mkdir -p /var/lib/redis \
    && touch /var/log/xdebug.log

# Copy configuration files (akan di-overwrite oleh volume mount di development)
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/writable \
    && chmod -R 777 /var/www/html/writable/cache \
    && chmod -R 777 /var/www/html/writable/logs \
    && chmod -R 777 /var/www/html/writable/session \
    && chmod -R 777 /var/www/html/writable/uploads \
    && chmod 777 /var/log/xdebug.log

# Expose ports
EXPOSE 80 443 9000 9090

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Entry point script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Start services with Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
