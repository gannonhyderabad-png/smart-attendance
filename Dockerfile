FROM php:8.2-cli

# Install system dependencies & PHP extensions (PDO MySQL, SQLite3, GD with WebP/JPEG/PNG, Zip, OPcache)
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql pdo_pgsql pdo_sqlite gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy all application files
COPY . /var/www/html/

# Create database and uploads directory with full permissions
RUN mkdir -p /var/www/html/database /var/www/html/public/uploads/avatars /var/www/html/public/uploads/punches \
    && touch /var/www/html/database/attendance.sqlite \
    && chmod -R 777 /var/www/html/database /var/www/html/public/uploads

EXPOSE 80 10000

# Start high-performance PHP concurrent web server on Render's dynamic $PORT
CMD ["sh", "-c", "PHP_CLI_SERVER_WORKERS=8 php -S 0.0.0.0:${PORT:-80} index.php"]
