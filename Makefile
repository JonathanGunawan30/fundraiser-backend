# Makefile for Fundraiser Application

# Variables
DOCKER_COMPOSE = docker compose
APP_SERVICE = app
DB_SERVICE = db
EXEC_APP = $(DOCKER_COMPOSE) exec $(APP_SERVICE)

.PHONY: help up down restart build ps logs shell install migrate migrate-fresh seed admin test clear optimize

help: ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start the application containers
	$(DOCKER_COMPOSE) up -d

down: ## Stop and remove containers
	$(DOCKER_COMPOSE) down

restart: ## Restart the application
	$(MAKE) down
	$(MAKE) up

build: ## Build or rebuild services
	$(DOCKER_COMPOSE) build

ps: ## List containers
	$(DOCKER_COMPOSE) ps

logs: ## Show application logs
	$(DOCKER_COMPOSE) logs -f $(APP_SERVICE)

shell: ## Enter the app container bash
	$(EXEC_APP) bash

install: ## Install composer and npm dependencies
	$(EXEC_APP) composer install
	$(EXEC_APP) npm install

migrate: ## Run database migrations
	$(EXEC_APP) php artisan migrate

migrate-fresh: ## Run migrations fresh with seeders
	$(EXEC_APP) php artisan migrate:fresh --seed

seed: ## Run database seeders
	$(EXEC_APP) php artisan db:seed

admin: ## Create a new admin user
	$(EXEC_APP) php artisan make:admin

test: ## Run tests
	$(EXEC_APP) php artisan test

optimize: ## Optimize the application (caching config, routes, etc.)
	$(EXEC_APP) php artisan optimize

clear: ## Clear all caches
	$(EXEC_APP) php artisan optimize:clear
	$(EXEC_APP) php artisan cache:clear
	$(EXEC_APP) php artisan config:clear
	$(EXEC_APP) php artisan route:clear

# Helper to setup the application for the first time
setup: ## Setup the application (build, up, install, key:generate, migrate)
	@if [ ! -f .env ]; then cp .env.example .env; fi
	$(MAKE) build
	$(MAKE) up
	$(MAKE) install
	$(EXEC_APP) php artisan key:generate
	$(MAKE) migrate-fresh
