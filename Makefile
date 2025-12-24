DOCKER_COMPOSE ?= docker compose
APP_SERVICE ?= app

.PHONY: up vite migrate seed pint pint-all phpstan shell db-shell

up:
	$(DOCKER_COMPOSE) up -d

vite:
	$(DOCKER_COMPOSE) exec $(APP_SERVICE) npm run dev

migrate:
	$(DOCKER_COMPOSE) exec $(APP_SERVICE) php artisan migrate --force

seed:
	$(DOCKER_COMPOSE) exec $(APP_SERVICE) php artisan db:seed --force

pint:
	$(DOCKER_COMPOSE) exec $(APP_SERVICE) ./vendor/bin/pint --dirty

pint-all:
	$(DOCKER_COMPOSE) exec $(APP_SERVICE) ./vendor/bin/pint

phpstan:
	$(DOCKER_COMPOSE) exec $(APP_SERVICE) ./vendor/bin/phpstan analyse

shell:
	$(DOCKER_COMPOSE) exec $(APP_SERVICE) /bin/bash

db-shell:
	@DB_USERNAME=$${DB_USERNAME:-app}; \
	DB_PASSWORD=$${DB_PASSWORD:-secret}; \
	DB_DATABASE=$${DB_DATABASE:-app}; \
	$(DOCKER_COMPOSE) exec db mysql -u$$DB_USERNAME -p$$DB_PASSWORD $$DB_DATABASE
