FROM php:8.2-apache

# 1. تثبيت الأدوات الأساسية
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    git \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. ضبط الذاكرة
RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/memory-limit.ini

WORKDIR /var/www/html

# 4. نسخ ملفات الـ Composer أولاً (خطوة حاسمة لتجنب الـ Cache المكسور)
COPY composer.json ./
# إذا كان لديك ملف composer.lock انسخه أيضاً، وإذا لم يوجد لا بأس
COPY composer.lock* ./

# 5. تشغيل التثبيت (أضفنا --ignore-platform-reqs لتجنب أي تعارض في النسخ)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --ignore-platform-reqs

# 6. نسخ بقية ملفات المشروع
COPY . .

# 7. إعدادات Apache والصلاحيات
RUN a2dismod mpm_event mpm_worker || true && a2enmod mpm_prefork rewrite
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]