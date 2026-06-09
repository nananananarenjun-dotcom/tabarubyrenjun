FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install intl zip pdo_pgsql

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install JS dependencies & build Vite assets
RUN npm install
RUN npm run build

RUN chmod -R 775 storage bootstrap/cache
RUN php artisan storage:link || true
RUN php artisan config:clear || true
RUN php artisan cache:clear || true

EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} -t public