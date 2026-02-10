#!/bin/sh

composer install --no-dev --no-interaction --optimize-autoloader

php -S 0.0.0.0:$PORT -t public
