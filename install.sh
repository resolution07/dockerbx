#!/bin/sh
set -eu

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Подключаем сайты из sites-available в sites-enabled
./docker/nginx/sites.sh
