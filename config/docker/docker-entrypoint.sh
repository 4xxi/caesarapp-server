#!/bin/sh

if [ "$APP_ENV" = 'prod' ]; then
    composer auto-scripts --no-interaction
else
    composer install --prefer-dist --no-progress --no-suggest --no-interaction
fi

chown -R www-data var
php-fpm