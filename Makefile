# Assessment Application Makefile
# Usage: make <target>

.PHONY: help up down restart rebuild logs shell-backend shell-frontend shell-db \
        migrate seed fresh install passport clear test test-unit test-feature

# Default target
help:
	@echo "Assessment Application - Available Commands"
	@echo "============================================"
	@echo ""
	@echo "Docker Commands:"
	@echo "  make up              - Start all containers"
	@echo "  make down            - Stop all containers"
	@echo "  make restart         - Restart all containers"
	@echo "  make rebuild         - Rebuild and restart all containers"
	@echo "  make logs            - View logs from all containers"
	@echo "  make logs-backend    - View backend logs only"
	@echo "  make logs-frontend   - View frontend logs only"
	@echo ""
	@echo "Shell Access:"
	@echo "  make shell-backend   - Open shell in backend container"
	@echo "  make shell-frontend  - Open shell in frontend container"
	@echo "  make shell-db        - Open MySQL shell in database container"
	@echo ""
	@echo "Laravel Commands:"
	@echo "  make install         - Install composer dependencies"
	@echo "  make migrate         - Run database migrations"
	@echo "  make seed            - Run database seeders"
	@echo "  make fresh           - Fresh migration with seeders"
	@echo "  make passport        - Install Laravel Passport"
	@echo "  make clear           - Clear all Laravel caches"
	@echo "  make key             - Generate application key"
	@echo ""
	@echo "Testing Commands:"
	@echo "  make test            - Run all tests"
	@echo "  make test-unit       - Run unit tests only"
	@echo "  make test-feature    - Run feature tests only"
	@echo "  make test-coverage   - Run tests with coverage report"
	@echo "  make test-filter filter=ClassName  - Run specific test"
	@echo ""
	@echo "Frontend Commands:"
	@echo "  make npm-install     - Install npm dependencies"
	@echo "  make npm-build       - Build frontend for production"
	@echo ""
	@echo "Setup Commands:"
	@echo "  make setup           - Full initial setup"
	@echo "  make reset           - Reset everything (WARNING: deletes data)"
	@echo ""

# ===================
# Docker Commands
# ===================

up:
	@echo "Starting containers..."
	docker-compose up -d
	@echo "Containers started!"
	@echo "Frontend: http://localhost:3000"
	@echo "Backend:  http://localhost:8000"

down:
	@echo "Stopping containers..."
	docker-compose down
	@echo "Containers stopped!"

restart:
	@echo "Restarting containers..."
	docker-compose restart
	@echo "Containers restarted!"

rebuild:
	@echo "Rebuilding and starting containers..."
	docker-compose down
	docker-compose up -d --build
	@echo "Containers rebuilt and started!"

logs:
	docker-compose logs -f

logs-backend:
	docker-compose logs -f backend

logs-frontend:
	docker-compose logs -f frontend

logs-db:
	docker-compose logs -f mariadb

# ===================
# Shell Access
# ===================

shell-backend:
	docker-compose exec backend bash

shell-frontend:
	docker-compose exec frontend sh

shell-db:
	docker-compose exec mariadb mysql -u admin -p0 assessment_db

# ===================
# Laravel Commands
# ===================

install:
	@echo "Installing composer dependencies..."
	docker-compose exec backend composer install
	@echo "Dependencies installed!"

migrate:
	@echo "Running migrations..."
	docker-compose exec backend php artisan migrate
	@echo "Migrations completed!"

seed:
	@echo "Running seeders..."
	docker-compose exec backend php artisan db:seed
	@echo "Seeding completed!"

fresh:
	@echo "Running fresh migration with seeders..."
	docker-compose exec backend php artisan migrate:fresh --seed
	@echo "Fresh migration completed!"

passport:
	@echo "Installing Laravel Passport..."
	docker-compose exec backend php artisan passport:install --force
	docker-compose exec backend php artisan passport:client --personal --name="Personal Access Client" --no-interaction || true
	@echo "Passport installed!"

clear:
	@echo "Clearing Laravel caches..."
	docker-compose exec backend php artisan config:clear
	docker-compose exec backend php artisan cache:clear
	docker-compose exec backend php artisan route:clear
	docker-compose exec backend php artisan view:clear
	@echo "Caches cleared!"

key:
	@echo "Generating application key..."
	docker-compose exec backend php artisan key:generate
	@echo "Application key generated!"

tinker:
	docker-compose exec backend php artisan tinker

routes:
	docker-compose exec backend php artisan route:list

# ===================
# Testing Commands
# ===================

test:
	@echo "Running all tests..."
	docker-compose exec -T backend php artisan test --parallel
	@echo "Tests completed!"

test-unit:
	@echo "Running unit tests..."
	docker-compose exec -T backend php artisan test --testsuite=Unit
	@echo "Unit tests completed!"

test-feature:
	@echo "Running feature tests..."
	docker-compose exec -T backend php artisan test --testsuite=Feature
	@echo "Feature tests completed!"

test-coverage:
	@echo "Running tests with coverage..."
	docker-compose exec -T backend php artisan test --coverage
	@echo "Coverage report generated!"

test-filter:
	@echo "Running filtered tests..."
	docker-compose exec backend php artisan test --filter=$(filter)

# ===================
# Frontend Commands
# ===================

npm-install:
	@echo "Installing npm dependencies..."
	docker-compose exec frontend npm install
	@echo "NPM dependencies installed!"

npm-build:
	@echo "Building frontend for production..."
	docker-compose exec frontend npm run build
	@echo "Frontend built!"

npm-lint:
	docker-compose exec frontend npm run lint

# ===================
# Setup Commands
# ===================

setup: up
	@echo ""
	@echo "Waiting for MariaDB to be ready..."
	@sleep 10
	@echo "Installing backend dependencies..."
	docker-compose exec -T backend composer install --no-interaction
	@echo "Generating application key..."
	docker-compose exec -T backend php artisan key:generate --force
	@echo "Running migrations..."
	docker-compose exec -T backend php artisan migrate --force
	@echo "Seeding database..."
	docker-compose exec -T backend php artisan db:seed --force
	@echo "Installing Passport..."
	docker-compose exec -T backend php artisan passport:install --force
	docker-compose exec -T backend php artisan passport:client --personal --name="Personal Access Client" --no-interaction || true
	@echo "Clearing caches..."
	docker-compose exec -T backend php artisan config:clear
	docker-compose exec -T backend php artisan cache:clear
	@echo ""
	@echo "=========================================="
	@echo "  Setup Complete!"
	@echo "=========================================="
	@echo ""
	@echo "Frontend: http://localhost:3000"
	@echo "Backend:  http://localhost:8000/api"
	@echo ""
	@echo "Test Users:"
	@echo "  admin@example.com / password (full access)"
	@echo "  user@example.com / password (read-only)"
	@echo ""

reset:
	@echo "WARNING: This will delete all data!"
	@read -p "Are you sure? [y/N] " confirm && [ "$$confirm" = "y" ] || exit 1
	docker-compose down -v
	@echo "All containers and volumes removed."
	@echo "Run 'make setup' to start fresh."

# ===================
# Status Commands
# ===================

status:
	docker-compose ps

health:
	@echo "Checking container health..."
	@docker-compose ps
	@echo ""
	@echo "Checking MariaDB..."
	@docker-compose exec -T mariadb mysqladmin ping -h localhost -u admin -p0 --silent && echo "MariaDB: OK" || echo "MariaDB: FAILED"
	@echo ""
	@echo "Checking Backend..."
	@curl -s http://localhost:8000 > /dev/null && echo "Backend: OK" || echo "Backend: FAILED"
	@echo ""
	@echo "Checking Frontend..."
	@curl -s http://localhost:3000 > /dev/null && echo "Frontend: OK" || echo "Frontend: FAILED"
