FROM php:8.2-apache

# 1. تثبيت أدوات النظام المطلوبة وإضافات قاعدة البيانات
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    git \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. تثبيت Composer رسمياً داخل الحاوية
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. ضبط المجلد الرئيسي
WORKDIR /var/www/html

# 4. نسخ ملفات المشروع أولاً (بما فيها composer.json)
COPY . .

# 5. تشغيل Composer لتثبيت المكتبات (PHPMailer وغيرها)
# هذا السطر هو الذي سيحل مشكلة "Class PHPMailer not found"
RUN composer install --no-dev --optimize-autoloader

# 6. إعداد موديولات Apache وضبط الصلاحيات
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork rewrite
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# 7. إعداد المنفذ وتشغيل السيرفر
EXPOSE 80
CMD ["apache2-foreground"]