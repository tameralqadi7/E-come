FROM php:8.2-apache

# 1. تثبيت الإضافات اللازمة لـ PHP وقاعدة البيانات
RUN docker-php-ext-install pdo pdo_mysql

# 2. تثبيت الأدوات اللازمة
RUN apt-get update && apt-get install -y git unzip

# 3. حل مشكلة MPM بشكل جذري: حذف ملفات الإعداد للموديلات الأخرى
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_worker.load || true
RUN a2enmod mpm_prefork

# 4. تفعيل خاصية الـ Rewrite
RUN a2enmod rewrite

# 5. ضبط مجلد العمل والصلاحيات
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]