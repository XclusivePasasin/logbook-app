# Usar imagen base de PHP con Apache
FROM php:8.1-apache-bullseye

WORKDIR /var/www/html

# Instalar dependencias del sistema (solo lo necesario)
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        libpng-dev \
        libzip-dev \
        unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Instalar solo las extensiones CRÍTICAS para Laravel
# (compilamos menos extensiones para evitar errores de I/O)
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    zip \
    gd

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer --version=2.7.7

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf && \
    echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/sites-available/000-default.conf

# Copiar archivos
COPY --chown=www-data:www-data . /var/www/html

# Instalar dependencias PHP
RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --optimize-autoloader \
    --no-dev \
    --no-interaction

# Permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cachear Laravel
RUN php artisan config:cache 2>/dev/null || true && \
    php artisan route:cache 2>/dev/null || true && \
    php artisan view:cache 2>/dev/null || true

EXPOSE 80

CMD ["apache2-foreground"]