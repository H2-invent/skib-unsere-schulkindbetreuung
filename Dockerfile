FROM thecodingmachine/php:8.2-v4-fpm-node22 AS builder
ARG VERSION=development

ENV PHP_EXTENSION_LDAP=1
ENV PHP_EXTENSION_INTL=1
ENV PHP_EXTENSION_BCMATH=1
ENV COMPOSER_MEMORY_LIMIT=-1
ENV PHP_EXTENSION_GD=1

COPY . /var/www/html

USER root

RUN npm install \
    && npm run build

RUN composer install --no-scripts

RUN sed -i "s/^laF_version=.*/laF_version=${VERSION}/" .env

RUN tar \
    --exclude='./.github' \
    --exclude='./.git' \
    --exclude='./node_modules' \
    --exclude='./var/cache' \
    --exclude='./var/log' \
    -zcvf /artifact.tgz .

FROM reg.h2-invent.com/public-system-design/debian-php83-cron-webserver:3.23.5
ARG VERSION=development

LABEL version="${VERSION}" \
    Maintainer="H2 invent GmbH" \
    Description="Docker Image der Anwendung Unsere Schulkindbetreuung" \
    org.opencontainers.version="${VERSION}" \
    org.opencontainers.image.title="Unsere Schulkindbetreuung" \
    org.opencontainers.image.license="BSL License" \
    org.opencontainers.image.vendor="H2 invent GmbH" \
    org.opencontainers.image.authors="Andreas Holzmann <support@h2-invent.com>" \
    org.opencontainers.image.source="https://github.com/h2-invent/skb" \
    org.opencontainers.image.documentation="https://unsere-schulkindbetreuung.de" \
    org.opencontainers.image.url="https://unsere-schulkindbetreuung.de"

USER root

RUN echo "Europe/Berlin" > /etc/timezone

RUN echo "" >> /etc/php/active/fpm/conf.d/custom.ini \
    && echo "opcache.enable=1" >> /etc/php/active/fpm/conf.d/custom.ini \
    && echo "opcache.enable_cli=1" >> /etc/php/active/fpm/conf.d/custom.ini \
    && echo "opcache.preload=/var/www/html/config/preload.php" >> /etc/php/active/fpm/conf.d/custom.ini \
    && echo "opcache.preload_user=nobody" >> /etc/php/active/fpm/conf.d/custom.ini \
    && echo "opcache.memory_consumption=256" >> /etc/php/active/fpm/conf.d/custom.ini \
    && echo "opcache.max_accelerated_files=32531" >> /etc/php/active/fpm/conf.d/custom.ini \
    && echo "opcache.interned_strings_buffer=32" >> /etc/php/active/fpm/conf.d/custom.ini \
    && echo "opcache.validate_timestamps=0" >> /etc/php/active/fpm/conf.d/custom.ini

USER nobody

COPY --from=builder /artifact.tgz artifact.tgz

RUN tar -zxvf artifact.tgz \
    && mkdir data \
    && mkdir -p var/log \
    && mkdir -p var/cache \
    && rm artifact.tgz

ENV STARTUP_COMMAND_0="php bin/console doc:mig:mig --no-interaction" \
    CRON_COMMAND_0="*/10 * * * * php /var/www/html/bin/console app:statistik:generate" \
    nginx_root_directory=/var/www/html/public \
    memory_limit=1024M \
    client_max_body_size=20M \
    post_max_size=20M \
    upload_max_filesize=20M \
    date_timezone=Europe/Berlin
