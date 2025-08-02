FROM php:8.3-fpm

# enable bash
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

RUN apt-get update \
    && apt-get install -y \
        gnupg \
        zlib1g-dev \
        g++ \
        git \
        python3 \
        libicu-dev \
        zip \
        libzip-dev \
        libmagickwand-dev \
        exiftool \
        vim \
        libpq-dev --no-install-recommends \
        openssh-client \
        locales \
        socat \
        rsync \
        lsb-release \
        wget \
        libwebp-dev \
        libxml2-dev

# Install Imagick from the source
RUN git clone https://github.com/Imagick/imagick.git --depth 1 /tmp/imagick && \
    cd /tmp/imagick && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    docker-php-ext-enable imagick

RUN pecl install redis \
    apcu \
    xdebug


RUN docker-php-ext-install \
        intl \
        opcache \
        pdo \
        pdo_mysql \
    && docker-php-ext-enable redis \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install gd \
    && docker-php-ext-enable gd \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure exif \
    && docker-php-ext-install exif \
    && docker-php-ext-configure sockets \
    && docker-php-ext-install sockets \
    && docker-php-ext-install sysvsem \
    && docker-php-ext-enable sysvsem \
    && docker-php-ext-install bcmath \
    && docker-php-ext-enable bcmath \
    && docker-php-ext-install pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install soap


ENV NODE_VERSION=12.22.12
RUN apt-get install -y curl
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
ENV NVM_DIR=/root/.nvm
RUN . "$NVM_DIR/nvm.sh" && nvm install ${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"

# yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install -y yarn
RUN rm -rf /var/lib/apt/lists/*

# composer
WORKDIR /var/www/
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# remove old node
RUN apt-get remove -y nodejs
RUN apt autoremove -y

# Add the Xdebug configuration 3.x
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Add the Xdebug configuration 2.x
#RUN echo -e "zend_extension=xdebug\nxdebug.remote_enable=1\nxdebug.remote_autostart=0\nxdebug.remote_connect_back=0\nxdebug.remote_port=9003\nxdebug.remote_log=/var/log/xdebug.log\nxdebug.remote_host=host.docker.internal" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini





