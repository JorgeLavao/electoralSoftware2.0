#!/bin/sh
set -e

cd /var/www/html

if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
    if [ "${BOOTSTRAP_COMPOSER:-0}" = "1" ]; then
        composer install --no-interaction --prefer-dist
    else
        echo "Waiting for Composer dependencies to be installed..."
        i=0
        until [ -f vendor/autoload.php ]; do
            i=$((i + 1))
            if [ "$i" -ge 60 ]; then
                echo "Timed out waiting for vendor/autoload.php"
                exit 1
            fi
            sleep 2
        done
    fi
fi

exec "$@"
