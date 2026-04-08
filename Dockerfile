FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpq5 \
    && docker-php-ext-install pdo_pgsql pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    Alias /backend /var/www/html/backend\n\
    <Directory /var/www/html/backend>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

COPY ./frontend /var/www/html/
COPY ./backend /var/www/html/backend/
COPY ./mock /var/www/html/mock

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

RUN echo "error_reporting = E_ALL" > /usr/local/etc/php/conf.d/custom.ini && \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/custom.ini && \
    echo "date.timezone = UTC" >> /usr/local/etc/php/conf.d/custom.ini

WORKDIR /var/www/html

EXPOSE 80