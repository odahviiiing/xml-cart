ARG php_version=7.4
FROM php:${php_version}-fpm

LABEL maintainer="Ryan Gellis <ryan.gellis@rmgmedia.com>"

RUN apt-get update && apt-get install -y \
  git \
  libxml2-dev \
  libxslt-dev \
  vim \
&& rm -rf /var/lib/apt/lists/*

RUN mkdir /var/www/xml-cart
RUN chown -R www-data:www-data /var/www/xml-cart

USER www-data

WORKDIR /var/www/xml-cart
