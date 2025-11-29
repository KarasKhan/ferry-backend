# Gunakan Image PHP 8.4 resmi dengan Apache
FROM php:8.4-apache

# 1. Install Library Sistem yang dibutuhkan (zip, png, git, dll)
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

# 2. Bersihkan cache instalasi biar ringan
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. Install Ekstensi PHP (Wajib buat Laravel & Filament)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# 4. Aktifkan Mod Rewrite Apache (Supaya URL Laravel jalan)
RUN a2enmod rewrite

# 5. Install Composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Setting Folder Kerja
WORKDIR /var/www/html

# 7. Setting Document Root ke folder /public (Standar Laravel)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 8. Copy semua kodingan ke dalam Docker
COPY . /var/www/html

# 9. Install Paket Composer (Production Mode)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 10. Fix Permission (PENTING: Agar Laravel bisa nulis log/cache)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Expose Port (Railway pakai PORT env, tapi default 80)
EXPOSE 80