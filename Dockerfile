FROM php:8.2-apache

# 1. تثبيت الإضافات الضرورية لقاعدة البيانات وضغط الملفات
RUN apt-get update && apt-get install -y \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. تفعيل موديول Rewrite و Prefork (لضمان عمل Apache بشكل مستقر)
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork rewrite

# 3. ضبط المجلد الرئيسي
WORKDIR /var/www/html

# 4. نسخ كل ملفات المشروع (تأكد أن مجلد PHPMailer موجود ضمنهم)
COPY . .

# 5. ضبط الصلاحيات للمجلد لضمان قدرة Apache على قراءة الملفات
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# 6. إعداد المنفذ وتشغيل السيرفر
EXPOSE 80
CMD ["apache2-foreground"]