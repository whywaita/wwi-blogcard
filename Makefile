.PHONY: help dev dev-build dev-down dev-destroy lint lint-fix test test-e2e build package clean composer-install

.DEFAULT_GOAL := help

# Help
help:
	@echo "WP Blogcard - Available Commands"
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
	@echo ""
	@echo "Testing:"
	@echo "  make test         Run PHP unit tests (via Docker)"
	@echo "  make test-e2e     Run Playwright E2E tests"
	@echo ""
	@echo "Build:"
	@echo "  make build        Build assets with webpack"
	@echo "  make package      Build and create dist/wp-blogcard.zip"
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
	@echo "🔍 Running all linters..."
	@echo "📜 Linting JavaScript..."
	npm run lint:js
	@echo "🎨 Linting CSS..."
	npm run lint:css
	@echo "🐘 Linting PHP..."
	docker compose run --rm php composer phpcs
	@echo "✅ All linters passed!"

lint-fix:
	@echo "🔧 Fixing lint issues..."
	npm run format
	docker compose run --rm php composer phpcbf || true
	@echo "✅ Lint fixes applied!"

# Testing
test: composer-install
	@echo "🧪 Running PHP tests..."
	docker compose run --rm php composer test
	@echo "✅ Tests completed!"

test-e2e:
	npm run test:e2e

# Build
build:
	npm run build

# Package
package: build
	@echo "📦 Creating plugin package..."
	rm -rf dist/
	mkdir -p dist
	zip -r dist/wp-blogcard.zip \
		wp-blogcard.php \
		includes/ \
		build/ \
		readme.txt \
		-x "*.git*" -x "*.DS_Store"
	@echo "✅ Package created: dist/wp-blogcard.zip"

# Clean
clean:
	rm -rf build/
	rm -rf node_modules/
	rm -rf vendor/
