# Gunakan Image PHP 8.4 dengan Apache
FROM php:8.4-apache

# 1. Install Library Sistem
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    unzip \
    curl \
    git \
    default-mysql-client

# 2. Bersihkan Cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install Ekstensi PHP (Termasuk Opcache)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

# 4. Aktifkan Mod Rewrite Apache
RUN a2enmod rewrite

# Konfigurasi Opcache untuk Performa Maksimal
RUN echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# 5. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Folder Kerja
WORKDIR /var/www/html

# 7. Setting Document Root Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update config default Apache
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf

# --- PERBAIKAN DI SINI (HTTPS & Routing) ---
# Kita tambahkan SetEnvIf agar Apache sadar protokol HTTPS dari Railway
RUN echo "<Directory /var/www/html/public>" > /etc/apache2/conf-available/laravel.conf \
 && echo "    Options Indexes FollowSymLinks" >> /etc/apache2/conf-available/laravel.conf \
 && echo "    AllowOverride All" >> /etc/apache2/conf-available/laravel.conf \
 && echo "    Require all granted" >> /etc/apache2/conf-available/laravel.conf \
 && echo "    SetEnvIf X-Forwarded-Proto https HTTPS=on" >> /etc/apache2/conf-available/laravel.conf \
 && echo "</Directory>" >> /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel
# ------------------------------------------

# 8. Copy Kodingan
COPY . /var/www/html

# 9. Install Paket Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# 10. Fix Permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Port Dinamis Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 12. START COMMAND (Updated Fix Alpine Error)
# HAPUS 'php artisan livewire:publish --assets' DARI SINI
CMD sh -c "php artisan migrate --force && php artisan db:seed --force && php artisan filament:upgrade && php artisan optimize:clear && apache2-foreground"