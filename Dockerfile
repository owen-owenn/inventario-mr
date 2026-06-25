# Usar la versión ligera y nativa de PHP
FROM php:8.2-cli

# Instalar los conectores para tu base de datos
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar todo tu inventario al servidor
COPY . /app
WORKDIR /app

# Arrancar el servidor nativo de PHP conectado directamente a Railway
CMD php -S 0.0.0.0:$PORT
