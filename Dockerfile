FROM php:8.2-apache

# تثبيت الإضافات اللازمة لـ PHP وقاعدة البيانات
RUN docker-php-ext-install pdo pdo_mysql

# تثبيت الأدوات اللازمة لـ Composer
RUN apt-get update && apt-get install -y git unzip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# حل مشكلة الـ MPM: تعطيل الموديلات المتعارضة وتفعيل mpm_prefork
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork

# تفعيل خاصية الـ Rewrite لـ Apache
RUN a2enmod rewrite

WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]