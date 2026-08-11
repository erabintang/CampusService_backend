#!/bin/sh
set -e

# Railway meng-inject PORT (port acak) dan merutekan traffic ke port itu.
# Sesuaikan listen nginx dengan $PORT (default 80 untuk lokal).
PORT="${PORT:-80}"
echo "[entrypoint] Starting nginx on port ${PORT}"

sed -i "s|listen 80;|listen ${PORT};|g" /etc/nginx/sites-enabled/default
sed -i "s|listen \[::\]:80;|listen [::]:${PORT};|g" /etc/nginx/sites-enabled/default

exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
