FROM php:8.2-apache

# Instalar conexiones a la Base de Datos
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todos tus archivos al servidor
COPY . /var/www/html/

# ¡LA MAGIA!: Obligar a Apache a usar el puerto de seguridad de Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
