# Gunakan Image PHP 8.4
FROM php:8.4-apache

# 1. Install Library
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

# 3. Install Ekstensi PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# 4. Aktifkan Mod Rewrite (Wajib buat Laravel)
RUN a2enmod rewrite

# 5. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Folder Kerja
WORKDIR /var/www/html

# --- BAGIAN PERBAIKAN UTAMA ---
# 7. Setting Document Root & Izin Akses .htaccess
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Ubah target folder di config default Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Buat Config Khusus untuk Izinkan URL Cantik Laravel
RUN echo "<Directory ${APACHE_DOCUMENT_ROOT}>" > /etc/apache2/conf-available/laravel.conf \
 && echo "    Options Indexes FollowSymLinks" >> /etc/apache2/conf-available/laravel.conf \
 && echo "    AllowOverride All" >> /etc/apache2/conf-available/laravel.conf \
 && echo "    Require all granted" >> /etc/apache2/conf-available/laravel.conf \
 && echo "</Directory>" >> /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel
# ------------------------------

# 8. Copy Kodingan
COPY . /var/www/html

# 9. Install Paket Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# 10. Fix Permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Port Dinamis Railway (PENTING)
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 12. Start
CMD ["sh", "-c", "apache2-foreground"]