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

# Pre-create uploads folder structure
RUN mkdir -p /var/www/html/public/uploads/avatars \
             /var/www/html/public/uploads/punches \
             /var/www/html/public/uploads/leaves \
    && chmod -R 777 /var/www/html/public/uploads

# Copy entrypoint and strip any Windows CRLF line endings so Linux sh never fails
COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80 10000

CMD ["sh", "/usr/local/bin/entrypoint.sh"]
