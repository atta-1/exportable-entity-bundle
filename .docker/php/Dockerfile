FROM php:8.2-cli

SHELL ["/bin/bash", "--login", "-c"]

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev libxml2-dev sudo \
    && docker-php-ext-install intl opcache zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN useradd -rm -d /home/ubuntu -s /bin/bash -g root -G sudo -u 1000 ubuntu
RUN echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers
USER ubuntu

WORKDIR /app
