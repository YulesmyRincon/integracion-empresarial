FROM php:8.1.29-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libonig-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar composer.json y composer.lock
COPY composer.json composer.lock* /var/www/html/

# Instalar dependencias
RUN composer install --no-interaction --prefer-dist --optimize-autoloader 

# Copiar el resto del código
COPY . /var/www/html

# Copiar configuración personalizada de Apache
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Activar mod_rewrite de Apache
RUN a2enmod rewrite

# Dar permisos a la carpeta pública
RUN chmod -R 755 /var/www/html/public

EXPOSE 80

CMD ["apache2-foreground"]
