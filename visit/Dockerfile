
FROM ubuntu:18.04

ARG DEBIAN_FRONTEND=noninteractive

LABEL description="This is a example Dockerfile for Apache."

RUN apt-get update && apt-get install -yq --no-install-recommends \
    apt-utils \
    curl \
    # Install git
    git \
    # Install apache
    apache2 \
    # Install php 7.2
    libapache2-mod-php7.2 \
    php7.2-cli \
    php7.2-json \
    php7.2-curl \
    php7.2-fpm \
    php7.2-gd \
    php7.2-ldap \
    php7.2-mbstring \
    php7.2-mysql \
    php7.2-soap \
    php7.2-sqlite3 \
    php7.2-xml \
    php7.2-zip \
    php7.2-intl \
    php7.2-bcmath \
    php-mongodb \
    php-imagick \
    # Install tools
    openssl \
    nano \
    cron \
    graphicsmagick \
    imagemagick \
    ghostscript \
    mysql-client \
    mongodb-clients \
    php-redis \
    iputils-ping \
    locales \
    sqlite3 \
    ca-certificates \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set locales
RUN locale-gen en_US.UTF-8 ko_KR.UTF-8

RUN a2enmod rewrite ssl

RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/7.2/apache2/php.ini
RUN sed -i 's/post_max_size = 8M/post_max_size = 150M/' /etc/php/7.2/apache2/php.ini

RUN sed -i 's/short_open_tag = Off/short_open_tag = On/' /etc/php/7.2/apache2/php.ini
RUN sed -i 's/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/' /etc/php/7.2/apache2/php.ini

# Fix timezone issue
ENV TZ=Asia/Seoul
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY ./000-default.conf /etc/apache2/sites-available/

EXPOSE 80 443

CMD ["apachectl", "-D", "FOREGROUND"]

