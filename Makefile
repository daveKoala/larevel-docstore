.PHONY: help up down restart bash cache-clear migrate migrate-fresh migrate-seed composer-install test watch logs tinker key-generate ps build clean mailpit

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

up: ## Start all containers
	docker-compose up -d

down: ## Stop all containers
	docker-compose down

restart: ## Restart all containers
	docker-compose restart

build: ## Build/rebuild containers
	docker-compose build --no-cache

bash: ## Open bash shell in app container
	docker-compose exec app bash

cache-clear: ## Clear all Laravel caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh database migrations (drops all tables)
	docker-compose exec app php artisan migrate:fresh

migrate-seed: ## Run migrations with seeders
	docker-compose exec app php artisan migrate --seed

fresh: ## Fresh database with seeders
	docker-compose exec app php artisan migrate:fresh --seed

composer-install: ## Install composer dependencies
	docker-compose exec app composer install

composer-update: ## Update composer dependencies
	docker-compose exec app composer update

test: ## Run PHPUnit tests
	docker-compose exec app php artisan test

watch: ## Watch and rerun tests on file changes (Usage: make watch FILE=tests/Unit/Services/TenantResolverTest.php)
	docker-compose exec app vendor/bin/phpunit-watcher watch $(if $(FILTER),--filter=$(FILTER)) $(if $(FILE),$(FILE))

tinker: ## Open Laravel tinker
	docker-compose exec app php artisan tinker

key-generate: ## Generate application key
	docker-compose exec app php artisan key:generate

logs: ## View container logs
	docker-compose logs -f

logs-app: ## View app container logs
	docker-compose logs -f app

logs-web: ## View webserver logs
	docker-compose logs -f webserver

logs-db: ## View database logs
	docker-compose logs -f db

ps: ## Show running containers
	docker-compose ps

clean: ## Remove all containers, volumes, and images
	docker-compose down -v --remove-orphans
	docker system prune -f

db-bash: ## Open MySQL shell
	docker-compose exec db mysql -u${DB_USERNAME:-laravel} -p${DB_PASSWORD:-secret} ${DB_DATABASE:-laravel}

mailpit: ## Open MailPit web interface in browser
	@echo "Opening MailPit at http://localhost:8026"
	@open http://localhost:8026
