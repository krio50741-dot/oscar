FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libzip-dev unzip \
 && docker-php-ext-install pdo_mysql zip \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*
COPY . /var/www/html/
RUN mkdir -p /var/www/html/tmp_import \
 && chown -R www-data:www-data /var/www/html/tmp_import \
 && chmod 775 /var/www/html/tmp_import
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
EXPOSE 80
CMD ["apache2-foreground"]
