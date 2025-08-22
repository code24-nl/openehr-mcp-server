ARG PHP_VERSION=8.4

#
# Application base
#
FROM php:${PHP_VERSION}-cli-alpine AS base
# Install extensions and tools
RUN set -eux \
    && apk add --no-cache \
      curl \
      zstd \
      gzip \
    && curl --etag-compare etag.txt --etag-save etag.txt --remote-name https://curl.se/ca/cacert.pem \
    && cp -f "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# Add production PHP INI overlays (keep extension configs clean in docker/php)
COPY docker/php/zz-overwrites.ini /usr/local/etc/php/conf.d/
ENV APP_ENV=production
# Defining XDG Base Directories
ENV XDG_CONFIG_HOME=/data/.config XDG_CACHE_HOME=/data/.cache
ENV APP_CACHE_DIR=$XDG_CACHE_HOME/app
RUN mkdir -m 0775 -p "$XDG_CONFIG_HOME" "$XDG_CACHE_HOME" "$APP_CACHE_DIR" \
    && chown -R www-data:www-data "$APP_CACHE_DIR"
# Source code location
WORKDIR /app
EXPOSE 8242


FROM base AS development
# Development-specific settings and tools only
ENV APP_ENV=development
# Install PHP extensions and minimal OS tools in a single layer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN set -eux \
    && cp -f "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && adduser -S -u 1000 -G www-data wsl-user \
    && apk add --no-cache \
      git \
    && install-php-extensions \
      xdebug \
      @composer \
    && mkdir -m 0775 -p "$XDG_CONFIG_HOME/composer" "$XDG_CACHE_HOME/composer" \
    && chown -R wsl-user:www-data "$XDG_CONFIG_HOME/composer" "$XDG_CACHE_HOME/composer"


#
# PHP Dependencies builder (production deps only)
#
FROM development AS vendor-builder
COPY composer.json ./composer.json
COPY server.php ./server.php
COPY src ./src
RUN --mount=type=cache,target=$XDG_CONFIG_HOME \
    --mount=type=cache,target=$XDG_CACHE_HOME \
    composer install --no-interaction --no-progress --no-ansi --no-scripts --no-dev --classmap-authoritative --optimize-autoloader


#
# Application (production)
#
FROM base AS production
COPY server.php ./server.php
COPY src ./src
COPY --from=vendor-builder /app/vendor/ ./vendor
USER www-data
CMD ["php", "server.php"]
