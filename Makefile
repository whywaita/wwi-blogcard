.PHONY: help dev dev-build dev-down dev-destroy lint lint-fix test test-e2e build package clean composer-install plugin-check prepare-plugin-dir

.DEFAULT_GOAL := help

# Plugin files to include in distribution
PLUGIN_NAME := wwi-blogcard
PLUGIN_FILES := wwi-blogcard.php includes/ build/ languages/ readme.txt

# Help
help:
	@echo "WWI Blogcard - Available Commands"
	@echo ""
	@echo "Development:"
	@echo "  make dev          Start development environment (docker compose up)"
	@echo "  make dev-build    Start with rebuild (docker compose up --build)"
	@echo "  make dev-down     Stop development environment"
	@echo "  make dev-destroy  Stop and remove volumes"
	@echo ""
	@echo "Linting:"
	@echo "  make lint         Run all linters (JS, CSS, PHP)"
	@echo "  make lint-fix     Auto-fix lint issues"
	@echo "  make plugin-check Run WordPress Plugin Check"
	@echo ""
	@echo "Testing:"
	@echo "  make test         Run PHP unit tests (via Docker)"
	@echo "  make test-e2e     Run Playwright E2E tests"
	@echo ""
	@echo "Build:"
	@echo "  make build        Build assets with webpack"
	@echo "  make package      Build and create dist/$(PLUGIN_NAME).zip"
	@echo "  make clean        Remove build artifacts and dependencies"
	@echo ""
	@echo "Dependencies:"
	@echo "  make composer-install  Install PHP dependencies via Docker"

# Development
dev:
	docker compose up -d

dev-build:
	docker compose up -d --build

dev-down:
	docker compose down

dev-destroy:
	docker compose down -v

# Dependencies
composer-install:
	docker compose run --rm php composer install

# Linting
lint:
	@echo "Running all linters..."
	@echo "Linting JavaScript..."
	npm run lint:js
	@echo "Linting CSS..."
	npm run lint:css
	@echo "Linting PHP..."
	docker compose run --rm php composer phpcs
	@echo "All linters passed!"

lint-fix:
	@echo "Fixing lint issues..."
	npm run format
	docker compose run --rm php composer phpcbf || true
	@echo "Lint fixes applied!"

# Testing
test: composer-install
	@echo "Running PHP tests..."
	docker compose run --rm php composer test
	@echo "Tests completed!"

test-e2e:
	npm run test:e2e

# Build
build:
	npm run build

# Prepare plugin directory (used by CI and plugin-check)
prepare-plugin-dir: build
	rm -rf plugin-check-dir
	mkdir -p plugin-check-dir/$(PLUGIN_NAME)/languages
	cp wwi-blogcard.php readme.txt plugin-check-dir/$(PLUGIN_NAME)/
	cp -r includes/ build/ plugin-check-dir/$(PLUGIN_NAME)/
	find languages/ -type f ! -name ".*" -exec cp {} plugin-check-dir/$(PLUGIN_NAME)/languages/ \; 2>/dev/null || true

# Package
package: prepare-plugin-dir
	@echo "Creating plugin package..."
	rm -rf dist/
	mkdir -p dist
	cd plugin-check-dir && zip -r ../dist/$(PLUGIN_NAME).zip $(PLUGIN_NAME) -x "*.git*" -x "*.DS_Store"
	rm -rf plugin-check-dir
	@echo "Package created: dist/$(PLUGIN_NAME).zip"

# Clean
clean:
	rm -rf build/
	rm -rf node_modules/
	rm -rf vendor/
	rm -rf plugin-check-dir/
	rm -rf dist/

# Plugin Check (local)
plugin-check: prepare-plugin-dir
	@echo "Running WordPress Plugin Check..."
	npm run wp-env start
	npm run wp-env run cli -- wp plugin check /var/www/html/wp-content/plugins/$(PLUGIN_NAME)/plugin-check-dir/$(PLUGIN_NAME)
	rm -rf plugin-check-dir
