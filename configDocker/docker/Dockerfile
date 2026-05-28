# Base

# maybe Need php 8.2 because xamp is with php 8.2 
FROM php:8.3-apache AS base

ENV COMPOSER_ALLOW_SUPERUSER=1

ENV APP_NAME="suivi-colis-iutv"
ENV DOCUMENT_ROOT="/var/www/html"
ENV APP_DIRECTORY="${DOCUMENT_ROOT}"

EXPOSE 80
EXPOSE 443
WORKDIR ${APP_DIRECTORY}

# git, unzip & zip are for composer
RUN apt-get update -qq && \
    apt-get install -qy \
    git \
    gnupg \
    unzip \
    zip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# PHP Extensions
RUN docker-php-ext-install -j$(nproc) opcache pdo_mysql
COPY configDocker/php.ini /usr/local/etc/php/conf.d/app.ini

# Apache
# COPY errors /errors
# Install laravel
# composer create-project --prefer-dist laravel/laravel nom_de_votre_projet
RUN chown -R www-data:www-data ${APP_DIRECTORY}
COPY configDocker/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY configDocker/apache.conf /etc/apache2/conf-available/${APP_NAME}.conf
COPY --chown=www-data:www-data app/index.php ${APP_DIRECTORY}/index.php
COPY --chown=www-data:www-data .env ${APP_DIRECTORY}/.env

RUN a2enmod rewrite remoteip && \
    a2enconf ${APP_NAME}

# Laravel tuto:
# RUN a2dissite 000-default.conf 
# RUN a2ensite ${APP_NAME} 

# To be sure apache2 configuration modifications is applied and to see apache config errors - on ubuntu : systemctl restart apach2 but on debian service apache2 restart
RUN service apache2 restart


# Production

#FROM base AS production