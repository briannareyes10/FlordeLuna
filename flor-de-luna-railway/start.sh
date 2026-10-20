#!/bin/sh
# Arranque en Railway: 1) crea/actualiza las tablas, 2) enciende Apache en el puerto de Railway.
set -e

PORT="${PORT:-80}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Si la base no responde, el sitio igual enciende (y Conection/salud.php mostrará el problema).
php /var/www/html/Conection/migrar.php || echo "[start] La migración falló; revisa las variables de MySQL en Railway."

# Deshabilitar todos los MPMs activos para evitar el error "More than one MPM loaded"
a2dismod -f mpm_event mpm_worker mpm_prefork >/dev/null 2>&1 || true

# Eliminar enlaces de configuración residuales de MPM
rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf

# Habilitar únicamente mpm_prefork
a2enmod mpm_prefork >/dev/null 2>&1 || true

echo "[start] MPM activos: $(ls /etc/apache2/mods-enabled | grep -i mpm | tr '\n' ' ')"
grep -rn "LoadModule mpm" /etc/apache2/apache2.conf /etc/apache2/conf-enabled /etc/apache2/sites-enabled 2>/dev/null || true

exec apache2-foreground
