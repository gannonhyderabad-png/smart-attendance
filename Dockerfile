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

# ─────────────────────────────────────────────────────────────────────────────
# IMPORTANT: Do NOT create or touch /var/www/html/database/attendance.sqlite
# here. The /database directory is mounted as a Render Persistent Disk.
# The entrypoint script initialises the DB only on first boot (when empty).
# ─────────────────────────────────────────────────────────────────────────────

# Pre-create uploads folder structure (only for the image in the build layer;
# at runtime the entrypoint ensures both /data/uploads and /data/database dirs)
RUN mkdir -p /var/www/html/public/uploads/avatars \
             /var/www/html/public/uploads/punches \
             /var/www/html/public/uploads/leaves \
    && chmod -R 777 /var/www/html/public/uploads

EXPOSE 80 10000

# Use the entrypoint so data directories are initialised before the server starts
COPY docker-entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# CMD is handled by the entrypoint script (exec php -S ...)
