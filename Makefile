COMPOSE = docker compose

build:
	$(COMPOSE) build

up:
	$(COMPOSE) up -d

build-up:
	$(COMPOSE) up -d --build

down:
	$(COMPOSE) down

reload:
	$(COMPOSE) restart

php-bash:
	$(COMPOSE) exec php-fpm bash

php-check:
	$(COMPOSE) exec php-fpm php /usr/local/bin/check-bitrix-extensions.php

redis-cli:
	$(COMPOSE) exec redis redis-cli

sites-link:
	./docker/nginx/sites.sh

sites-list:
	./docker/nginx/sites.sh list

remove-logs:
	echo "Remove logs..."
	sudo rm -rf ./docker/nginx/log/* ./docker/php/*/log/*
