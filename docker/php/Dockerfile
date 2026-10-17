FROM php:8.3-apache

# Install system deps + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev \
        libzip-dev \
        unzip \
        git \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules we need
RUN a2enmod rewrite headers

# Point Apache's DocumentRoot at /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides in the DocumentRoot.
# Without this, Apache ignores every .htaccess file (default is
# AllowOverride None), which breaks extensionless URLs and our
# sensitive-file blocks in public/.htaccess.
RUN { \
        echo '<Directory /var/www/html/public>'; \
        echo '    AllowOverride All'; \
        echo '    Require all granted'; \
        echo '</Directory>'; \
    } > /etc/apache2/conf-available/overrides.conf \
 && a2enconf overrides

# Development-friendly php.ini
RUN { \
        echo 'display_errors=On'; \
        echo 'display_startup_errors=On'; \
        echo 'error_reporting=E_ALL'; \
        echo 'log_errors=On'; \
        echo 'error_log=/dev/stderr'; \
    } > /usr/local/etc/php/conf.d/zz-dev.ini

WORKDIR /var/www/html