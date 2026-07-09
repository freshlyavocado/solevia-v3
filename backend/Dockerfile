FROM php:8.4-fpm-alpine AS builder 

RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    linux-headers \
    icu-dev \
    libzip-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev


FROM php:8.4-fpm-alpine

# Install runtime libraries DAN dev packages untuk kompilasi ekstensi
RUN apk update && apk add --no-cache \
    libpng \
    libpng-dev \
    oniguruma \
    oniguruma-dev \
    libxml2 \
    libxml2-dev \
    icu-libs \
    icu-dev \
    libzip \
    libzip-dev \
    linux-headers \
    # Install ekstensi PHP (WAJIB di stage ini juga, karena tidak ikut tercopy dari builder)
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip \
    # Hapus dev packages setelah kompilasi selesai (untuk mengecilkan ukuran image)
    && apk del libpng-dev oniguruma-dev libxml2-dev icu-dev libzip-dev linux-headers \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www

COPY --from=builder /var/www /var/www

# Buat .env dari .env.example jika belum ada
RUN cp -n /var/www/.env.example /var/www/.env || true

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD [ "php-fpm" ]
