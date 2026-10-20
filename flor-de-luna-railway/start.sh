#!/bin/sh
# Arranque en Railway: 1) crea/actualiza las tablas, 2) enciende Apache en el puerto de Railway.
set -e

PORT="${PORT:-80}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Si la base no responde, el sitio igual enciende (y Conection/salud.php mostrará el problema).
php /var/www/html/Conection/migrar.php || echo "[start] La migración falló; revisa las variables de MySQL en Railway."

exec apache2-foreground
