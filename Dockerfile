FROM php:8.4.6-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www

# Copia os arquivos da aplicação para o container
COPY . .

# Instala as dependências do Laravel
RUN composer install --ignore-platform-reqs

# Permissões para o Laravel
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

# Comando padrão para iniciar o servidor
CMD php artisan serve --host=0.0.0.0 --port=80
