FROM php:8.1-apache

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libonig-dev default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar composer para instalar dependencias en build
COPY composer.json composer.lock* /var/www/html/
RUN composer install --no-interaction --prefer-dist --optimize-autoloader || true

# Copiar el resto del código
COPY . /var/www/html

# Habilitar rewrite para rutas bonitas si es necesario
RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]
