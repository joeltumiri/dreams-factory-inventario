FROM php:8.2-apache

# Extensiones minimas para Yii2 + MySQL (build mas rapido en entorno dev)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev unzip git \
    && docker-php-ext-install \
        pdo_mysql mysqli mbstring opcache \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Document root -> /var/www/html/web (entry point de Yii2)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

# Permitir .htaccess (AllowOverride All)
RUN printf '<Directory /var/www/html/web>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' \
    > /etc/apache2/conf-available/yii-override.conf \
    && a2enconf yii-override

WORKDIR /var/www/html

# php.ini: subir límites razonables para dev
RUN { \
        echo 'memory_limit=512M'; \
        echo 'upload_max_filesize=32M'; \
        echo 'post_max_size=32M'; \
        echo 'display_errors=On'; \
        echo 'date.timezone=America/La_Paz'; \
    } > /usr/local/etc/php/conf.d/zz-dreams-factory.ini

EXPOSE 80
CMD ["apache2-foreground"]
