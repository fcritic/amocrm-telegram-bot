# Образ php + fpm + alpine из внешнего репозитория
FROM php:8.2-fpm-alpine AS base

# Задаем расположениие рабочей директории 
ENV WORK_DIR=/var/www/application

RUN apk update && apk add --no-cache --virtual .build-deps \
    linux-headers \
    build-base \
    mysql-dev \
    && docker-php-ext-install -j$(nproc) pdo \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install -j$(nproc) sockets \
    && apk del --no-cache .build-deps

FROM base

# Указываем, что текущая папка проекта копируется в рабочию директорию контейнера https://docs.docker.com/engine/reference/builder/#copy
COPY . ${WORK_DIR}

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
