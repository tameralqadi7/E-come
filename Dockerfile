FROM php:8.2-apache

# 1. تثبيت الإضافات الضرورية وأداة unzip (مهمة لـ Composer)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. تثبيت Composer رسمياً داخل الحاوية
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. تفعيل موديول Rewrite
RUN a2enmod rewrite

# 4. ضبط المجلد الرئيسي
WORKDIR /var/www/html

# 5. نسخ ملفات الإعداد أولاً لتسريع البناء
COPY composer.json ./

# 6. تشغيل Composer install (سيحل مشكلة PHPMailer تلقائياً)
# نستخدم --no-scripts لتجنب أي تعارض في البداية
RUN composer install --no-interaction --no-scripts --optimize-autoloader || echo "Ignore lock error"

# 7. نسخ باقي ملفات المشروع
COPY . .

# 8. ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]