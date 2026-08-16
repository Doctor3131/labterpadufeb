# LabDigitalFEB — Docker convenience commands
#   prod (default): baked image, no bind mounts, no vite  (docker-compose.yml)
#   dev      :      bind-mounted source + vite HMR        (+ docker-compose.dev.yml)
#
# Usage:
#   make up                # prod (foreground)
#   make up MODE=dev       # dev (foreground)
#   make up-d MODE=dev     # dev (background)
#   make seed, make db-shell, ...

.DEFAULT_GOAL := help

COMPOSE := docker compose --env-file .env.docker
MODE ?= prod
COMPOSE_FILES := -f docker-compose.yml
ifeq ($(MODE),dev)
COMPOSE_FILES += -f docker-compose.dev.yml
endif
ALL_FILES := -f docker-compose.yml -f docker-compose.dev.yml

help: ## Show this help
	@echo "Usage: make <target> [MODE=dev|prod]  (default MODE=prod)"
	@echo ""
	@echo "  prod: baked image, no bind mounts.  dev: bind mounts + vite HMR."
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  %-15s %s\n", $$1, $$2}'

## ---- Setup ----------------------------------------------------------------

env: ## Create .env.docker from template (fails if already exists)
	@test ! -f .env.docker && cp .env.docker.example .env.docker && echo "Created .env.docker - edit then run 'make up'" || echo ".env.docker already exists"

## ---- Stack lifecycle ------------------------------------------------------

up: ## Build & start the stack (default prod; MODE=dev for dev)
	@$(COMPOSE) $(COMPOSE_FILES) up --build

up-d: ## Build & start the stack in background (default prod)
	@$(COMPOSE) $(COMPOSE_FILES) up --build -d

down: ## Stop all containers (prod + dev)
	@$(COMPOSE) $(ALL_FILES) down

clean: ## Stop everything and remove named volumes (destructive)
	@$(COMPOSE) $(ALL_FILES) down -v

## ---- Common ---------------------------------------------------------------

logs: ## Tail app (+ db, node) logs
	@$(COMPOSE) $(COMPOSE_FILES) logs -f

ps: ## Show running services
	@$(COMPOSE) $(ALL_FILES) ps

build: ## Build images
	@$(COMPOSE) $(COMPOSE_FILES) build

app: ## Open a shell in the app container
	@$(COMPOSE) $(COMPOSE_FILES) exec app bash

tinker: ## Run artisan tinker
	@$(COMPOSE) $(COMPOSE_FILES) exec app php artisan tinker

artisan: ## Run any artisan command: make artisan CMD="route:list"
	@$(COMPOSE) $(COMPOSE_FILES) exec app php artisan $(CMD)

migrate: ## Run database migrations
	@$(COMPOSE) $(COMPOSE_FILES) exec app php artisan migrate --force

fresh: ## Drop all tables and re-migrate (destructive)
	@$(COMPOSE) $(COMPOSE_FILES) exec app php artisan migrate:fresh --force

seed: ## Run database seeders (idempotent)
	@$(COMPOSE) $(COMPOSE_FILES) exec app php artisan db:seed --force

db-shell: ## Open a MySQL shell inside the db container
	@$(COMPOSE) $(COMPOSE_FILES) exec db sh -c 'mysql -u"$$MYSQL_USER" -p"$$MYSQL_PASSWORD" "$$MYSQL_DATABASE"'