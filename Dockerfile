FROM php:8.2-apache

# 1. Apagar módulos MPM conflictivos y asegurar el correcto
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# 2. Instalar extensiones para la Base de Datos
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 3. Copiar los archivos del proyecto al servidor
COPY . /var/www/html/

# 4. Configurar el puerto dinámico de Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
