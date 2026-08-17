FROM php:8.2-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    nodejs \
    npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        xml \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project
COPY . .

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

# Install frontend dependencies and build
RUN npm ci
RUN npm run build

# Laravel permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Aiven MySQL CA certificate
COPY docker/aiven-ca.pem /etc/ssl/certs/aiven-ca.pem

# Verify Aiven CA certificate exists
RUN ls -lah /etc/ssl/certs/aiven-ca.pem \
    && head -n 2 /etc/ssl/certs/aiven-ca.pem

# Nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Startup script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 10000

CMD ["/start.sh"]