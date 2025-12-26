FROM php:8.2-apache

# 1. تثبيت إضافات قاعدة البيانات
RUN docker-php-ext-install pdo pdo_mysql

# 2. تنظيف موديولات MPM المتعارضة (طريقة مختصرة وآمنة)
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork rewrite

# 3. ضبط المجلد الرئيسي
WORKDIR /var/www/html

# 4. نسخ الملفات
COPY . .

# 5. منح الصلاحيات الكاملة
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# 6. إعداد المنفذ
EXPOSE 80

# 7. تشغيل السيرفر
CMD ["apache2-foreground"]