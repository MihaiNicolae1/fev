FROM php:8.0-fpm-bullseye

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PCOV for code coverage (faster than Xdebug)
RUN pecl install pcov && docker-php-ext-enable pcov

# Install Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 8000
EXPOSE 8000

# Create entrypoint script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Create .env from env.example if it does not exist\n\
if [ ! -f ".env" ] && [ -f "env.example" ]; then\n\
    cp env.example .env\n\
    echo "Created .env from env.example"\n\
fi\n\
\n\
# Install composer dependencies if vendor directory does not exist\n\
if [ ! -d "vendor" ] && [ -f "composer.json" ]; then\n\
    echo "Installing composer dependencies..."\n\
    composer install --no-interaction --prefer-dist --optimize-autoloader\n\
fi\n\
\n\
# Generate application key if not set\n\
if [ -f ".env" ] && ! grep -q "^APP_KEY=base64:" .env; then\n\
    echo "Generating application key..."\n\
    php artisan key:generate --force\n\
fi\n\
\n\
# Start Laravel development server\n\
exec php artisan serve --host=0.0.0.0 --port=8000\n\
' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
