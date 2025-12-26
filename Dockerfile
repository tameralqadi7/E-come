FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    unzip curl git \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ضبط حد الذاكرة قبل التثبيت
RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/memory-limit.ini

WORKDIR /var/www/html
COPY . .

# الأمر المحدث والأكثر استقراراً
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork rewrite
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]