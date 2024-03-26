FROM ubuntu:21.04
FROM php:7.3-fpm

COPY composer.lock composer.json /var/www/hrml/

USER root

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
        build-essential \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        locales \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        libmcrypt-dev \
        libc-client-dev \
        libkrb5-dev\
        libxslt-dev \
        zip \
        jpegoptim optipng pngquant gifsicle \
        curl \
        vim \
        git \
        unzip\
        libmagickwand-dev \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -sL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs \
    && apt-get -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && mkdir -p /usr/src/php/ext/imagick; \
       curl -fsSL https://github.com/Imagick/imagick/archive/06116aa24b76edaf6b1693198f79e6c295eda8a9.tar.gz | tar xvz -C "/usr/src/php/ext/imagick" --strip 1;


RUN docker-php-source extract \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install mysqli \
    && docker-php-ext-install zip \
    && docker-php-ext-install exif \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install calendar \
    && docker-php-ext-install gd \
    && docker-php-ext-install gettext \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap \
    && docker-php-ext-install shmop \
    && docker-php-ext-install sockets \
    && docker-php-ext-install sysvmsg sysvsem sysvshm \
    && docker-php-ext-install xsl \
    && docker-php-ext-install imagick \
    && docker-php-source delete \
# Add user for laravel application
    && groupadd --force -g 1000 sail \
    && useradd -u 1000 -ms /bin/bash -g sail sail

# Copy existing application directory contents
COPY . /var/www/html

RUN composer install

# Copy existing application directory permissions
# COPY --chown=sail:sail . /var/www/html

# Change current user to www
USER sail

# # Expose port 9000 and start php-7.1 server
EXPOSE 9000
CMD ["php-fpm"]
