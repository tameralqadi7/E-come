FROM php:8.2-apache

# 1. تثبيت إضافات قاعدة البيانات
RUN docker-php-ext-install pdo pdo_mysql

# 2. حل مشكلة MPM بشكل جذري (حذف الملفات المتعارضة)
# هذا السطر يضمن عدم وجود أكثر من موديل محمل
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf || true \
    && a2enmod mpm_prefork

# 3. تفعيل خاصية الـ Rewrite
RUN a2enmod rewrite

# 4. ضبط البورت بشكل ديناميكي (لحل 502)
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

# 5. المجلد والصلاحيات
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html

# إعداد المتغير الافتراضي للبورت
ENV PORT 80
EXPOSE 80

CMD ["apache2-foreground"]