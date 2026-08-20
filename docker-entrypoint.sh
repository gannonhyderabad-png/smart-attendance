#!/bin/bash
set -e

# Support Render dynamic port assignment
PORT_TO_USE="${PORT:-80}"
sed -i "s/Listen [0-9]*/Listen ${PORT_TO_USE}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT_TO_USE}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure directories and permissions
mkdir -p /var/www/html/database /var/www/html/public/uploads/avatars /var/www/html/public/uploads/punches
chown -R www-data:www-data /var/www/html/database /var/www/html/public/uploads
chmod -R 777 /var/www/html/database /var/www/html/public/uploads

exec "$@"
