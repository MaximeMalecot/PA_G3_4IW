FROM php:8.0-fpm

COPY wait-for-it.sh /usr/bin/wait-for-it

RUN chmod +x /usr/bin/wait-for-it

RUN apt-get update && \
    apt-get install -y --no-install-recommends libssl-dev zlib1g-dev curl git dpkg wget unzip netcat libxml2-dev libpq-dev libzip-dev && \
    pecl install apcu && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) zip opcache intl pdo_pgsql pgsql && \
    docker-php-ext-enable apcu pdo_pgsql sodium && \
    wget https://github.com/elastic/apm-agent-php/releases/download/v1.5/apm-agent-php_1.5_all.deb && \
    dpkg -i apm-agent-php_1.5_all.deb && \
    echo 'elastic_apm.server_url="http://localhost:8200"' >> "$PHP_INI_DIR/php.ini" && \
    echo 'elastic.apm.secret_token=""' >> "$PHP_INI_DIR/php.ini" && \
    echo 'elastic_apm.service_name="My service"' >> "$PHP_INI_DIR/php.ini" && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

CMD composer i -o ; wait-for-it db:5432 -- bin/console doctrine:migrations:migrate ;  php-fpm

EXPOSE 9000
