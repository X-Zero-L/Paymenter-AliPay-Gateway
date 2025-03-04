FROM ghcr.io/paymenter/paymenter:latest

WORKDIR /var/www/paymenter

COPY composer.json /var/www/paymenter/composer.json

USER        caddy
ENV         USER=caddy
RUN         composer update --no-dev --optimize-autoloader
RUN         composer install --no-dev --optimize-autoloader \
    && rm -rf bootstrap/cache/*.php \
    && rm -rf storage/logs/*.log

EXPOSE      8080
CMD         ["/usr/bin/supervisord", "--configuration=/etc/supervisord.conf"]
