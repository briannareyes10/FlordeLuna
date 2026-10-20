# Flor de Luna — imagen para Railway (PHP 8.3 + Apache)
FROM php:8.3-apache

# Extensión de PHP para hablar con MySQL
RUN docker-php-ext-install pdo_mysql

# Permite que el .htaccess proteja las carpetas internas
RUN sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && a2enmod headers \
    && rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod mpm_prefork

WORKDIR /var/www/html
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html && sed -i 's/\r$//' /var/www/html/start.sh \
    && chmod +x /var/www/html/start.sh

# Railway define la variable PORT; start.sh pone a Apache a escuchar en ese puerto
CMD ["/var/www/html/start.sh"]

# Railway evitar errores
