FROM php:8.2-apache

# تثبيت الإضافات اللازمة لـ PHP وقاعدة البيانات
RUN docker-php-ext-install pdo pdo_mysql

# تثبيت الأدوات اللازمة لـ Composer (git, unzip)
RUN apt-get update && apt-get install -y \
    git \
    unzip

# تثبيت Composer رسمياً داخل الحاوية
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تفعيل خاصية الـ Rewrite لـ Apache (مهم جداً للروابط)
RUN a2enmod rewrite

# ضبط صلاحيات المجلد لضمان عدم حدوث خطأ 403 Forbidden
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# إخبار الحاوية بالاستماع لمنفذ 80 (الذي وضعه Railway)
EXPOSE 80

# أمر تشغيل Apache في الواجهة لضمان استمرار الحاوية
CMD ["apache2-foreground"]