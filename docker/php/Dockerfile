FROM php:8.3-fpm

ENV TZ=Asia/Tokyo

RUN set -eux; \
  apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
  && docker-php-ext-install -j"$(nproc)" zip \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer（署名検証付き）
RUN set -eux; \
EXPECTED_SIGNATURE="$(curl -fsSL https://composer.github.io/installer.sig)"; \
curl -fsSL -o composer-setup.php https://getcomposer.org/installer; \
ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"; \
if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then \
  echo 'ERROR: Invalid installer signature' >&2; \
  rm -f composer-setup.php; \
  exit 1; \
fi; \
php composer-setup.php --install-dir=/usr/bin --filename=composer; \
rm -f composer-setup.php; \
composer --version

WORKDIR /var/www/html

COPY ./php.ini /usr/local/etc/php/php.ini

EXPOSE 9000
