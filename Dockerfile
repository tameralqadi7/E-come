FROM php:8.2-apache

# 1. تثبيت الإضافات اللازمة لـ PHP وقاعدة البيانات
RUN docker-php-ext-install pdo pdo_mysql

# 2. تثبيت الأدوات اللازمة
RUN apt-get update && apt-get install -y git unzip

# 3. حل مشكلة MPM وتفعيل الموديلات الضرورية
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork
RUN a2enmod rewrite

# 4. الربط الديناميكي للبورت (حل مشكلة 502)
# نخبر أباتشي أن يستخدم البورت الذي يحدده Railway تلقائياً
RUN sed -i 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf
RUN sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g' /etc/apache2/sites-available/000-default.conf

# 5. ضبط مجلد العمل والصلاحيات
WORKDIR /var/www/html
COPY . .
RUN chown -R www-data:www-data /var/www/html

# ملاحظة: Railway سيقوم بتمرير قيمة الـ PORT تلقائياً
EXPOSE 80

CMD ["apache2-foreground"]