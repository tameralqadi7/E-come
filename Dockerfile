FROM php:8.2-apache
# تثبيت إضافات PDO لتمكين PHP من الاتصال بـ MySQL
RUN docker-php-ext-install pdo pdo_mysql
# تفعيل خاصية rewrite لـ Apache (مهمة للـ API و Routing)
RUN a2enmod rewrite
# تحديد مكان الكود داخل الحاوية
WORKDIR /var/www/html