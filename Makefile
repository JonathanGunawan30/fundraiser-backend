# Variables
DOCKER_COMPOSE = docker compose
APP_SERVICE = app
EXEC_APP = $(DOCKER_COMPOSE) exec $(APP_SERVICE)

.PHONY: help pull up down restart ps logs shell migrate migrate-fresh seed admin clear optimize deploy

help: ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

pull: ## Pull the latest image from registry
	$(DOCKER_COMPOSE) pull

up: ## Start the application containers
	$(DOCKER_COMPOSE) up -d

down: ## Stop and remove containers
	$(DOCKER_COMPOSE) down

restart: ## Restart the application
	$(MAKE) down
	$(MAKE) up

ps: ## List containers
	$(DOCKER_COMPOSE) ps

logs: ## Show application logs
	$(DOCKER_COMPOSE) logs -f $(APP_SERVICE)

shell: ## Enter the app container bash
	$(EXEC_APP) bash

migrate: ## Run database migrations
	$(EXEC_APP) php artisan migrate --force

migrate-fresh: ## DANGER: Drop all tables and re-migrate with seeders
	$(EXEC_APP) php artisan migrate:fresh --seed --force

seed: ## Run database seeders
	$(EXEC_APP) php artisan db:seed --force

admin: ## Create a new admin user
	$(EXEC_APP) php artisan make:admin

optimize: ## Optimize the application (caching config, routes, etc.)
	$(EXEC_APP) php artisan optimize

clear: ## Clear all caches
	$(EXEC_APP) php artisan optimize:clear

deploy: ## Full deploy: pull latest image, restart, migrate
	$(MAKE) pull
	$(MAKE) down
	$(MAKE) up
	$(EXEC_APP) php artisan migrate --force
