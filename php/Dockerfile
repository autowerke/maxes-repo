FROM php:8.2-fpm-alpine

# Install dependencies untuk compile extension (autoconf, gcc, dll)
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Hapus sisa build biar image tetep kecil
    && apk del pcre-dev $PHPIZE_DEPS

# Install driver mysql (mysqli & pdo_mysql) agar mirip XAMPP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# (Opsional) Aktifkan GD Library jika projectmu pakai gambar/captcha
RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd