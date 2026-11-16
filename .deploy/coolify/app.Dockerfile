# Staging image for Coolify. Build context is the repository root
# (see compose.yaml), so paths below are relative to it.
FROM php:8.3-apache

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
# Composer is held at 2.8. From 2.9 it refuses to install any version with an open
# security advisory, and every Laravel 10 release has some (fixed only in 12+), so
# the dependencies in composer.json cannot be resolved with a newer Composer.
COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

# gd + imagick: images, PDFs and PNG QR codes. zip: Excel import/export.
# calendar: required by khaled.alshamaa/ar-php (Arabic text in PDFs).
RUN apt-get update \
 && apt-get install -y --no-install-recommends git unzip supervisor \
 && rm -rf /var/lib/apt/lists/* \
 && install-php-extensions gd imagick zip intl pdo_mysql mysqli bcmath calendar exif pcntl sockets opcache \
 && a2enmod rewrite headers remoteip \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY .deploy/coolify/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY .deploy/coolify/apache.conf /etc/apache2/sites-available/000-default.conf
COPY .deploy/coolify/supervisord.conf /etc/supervisor/app.conf
COPY .deploy/coolify/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1

# Dependencies first so this layer is reused when only application code changes.
# composer.lock is not committed, so versions are resolved at build time.
# Scripts are skipped here because package discovery boots the app, which needs
# the database; the entrypoint runs it at start.
COPY composer.json ./
RUN composer install --no-interaction --no-progress --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-scripts \
 && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
ENTRYPOINT ["app-entrypoint"]
