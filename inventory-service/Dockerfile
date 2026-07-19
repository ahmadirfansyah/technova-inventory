FROM php:8.2-cli

RUN apt-get update && apt-get install -y unzip $PHPIZE_DEPS && rm -rf /var/lib/apt/lists/* \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && echo "apc.enable_cli=1" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /app

EXPOSE 8081
CMD ["php", "-S", "0.0.0.0:8081", "-t", "public"]
