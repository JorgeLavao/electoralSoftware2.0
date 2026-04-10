#!/bin/sh
set -e

cd /var/www/html

if [ -f package-lock.json ] && { [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; }; then
    npm ci
elif [ -f package.json ] && { [ ! -d node_modules ] || [ -z "$(ls -A node_modules 2>/dev/null)" ]; }; then
    npm install
fi

exec "$@"
