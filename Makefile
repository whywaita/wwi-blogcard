# ==============================================================================
# WWI Blogcard - Makefile
# ==============================================================================

.DEFAULT_GOAL := help

# ==============================================================================
# Variables
# ==============================================================================

PLUGIN_NAME := wwi-blogcard

# wp-env commands
WP_ENV := npm run wp-env
WP_ENV_RUN := $(WP_ENV) run cli --

# Container paths
CONTAINER_PLUGIN_PATH := /var/www/html/wp-content/plugins/$(PLUGIN_NAME)
CONTAINER_VENDOR_BIN := $(CONTAINER_PLUGIN_PATH)/vendor/bin

# ==============================================================================
# Help
# ==============================================================================

.PHONY: help

help:
	@echo "WWI Blogcard - Available Commands"
	@echo ""
	@echo "Development:"
	@echo "  make dev             Start development environment (docker compose up)"
	@echo "  make dev-build       Start with rebuild (docker compose up --build)"
	@echo "  make dev-down        Stop development environment"
	@echo "  make dev-destroy     Stop and remove volumes"
	@echo ""
	@echo "Linting:"
	@echo "  make lint            Run all linters (JS, CSS, PHP)"
	@echo "  make lint-js         Run JavaScript linter"
	@echo "  make lint-css        Run CSS linter"
	@echo "  make lint-php        Run PHP CodeSniffer (requires PHP)"
	@echo "  make lint-php-local  Run PHP CodeSniffer (via wp-env)"
	@echo "  make lint-fix        Auto-fix lint issues"
	@echo ""
	@echo "Plugin Check:"
	@echo "  make plugin-check    Run WordPress Plugin Check (dev environment)"
	@echo "  make plugin-check-dist  Run Plugin Check on distribution package"
	@echo ""
	@echo "Testing:"
	@echo "  make test            Run PHP unit tests (via Docker)"
	@echo "  make test-e2e        Run Playwright E2E tests"
	@echo ""
	@echo "Build:"
	@echo "  make build           Build assets with webpack"
	@echo "  make package         Build and create dist/$(PLUGIN_NAME).zip"
	@echo "  make clean           Remove build artifacts and dependencies"
	@echo ""
	@echo "Review:"
	@echo "  make review-check    Run all checks for WordPress.org review submission"
	@echo ""
	@echo "Dependencies:"
	@echo "  make composer-install  Install PHP dependencies via Docker"

# ==============================================================================
# Development
# ==============================================================================

.PHONY: dev dev-build dev-down dev-destroy

dev:
	docker compose up -d

dev-build:
	docker compose up -d --build

dev-down:
	docker compose down

dev-destroy:
	docker compose down -v

# ==============================================================================
# Dependencies
# ==============================================================================

.PHONY: composer-install

composer-install:
	docker compose run --rm php composer install

# ==============================================================================
# Linting
# ==============================================================================

.PHONY: lint lint-js lint-css lint-php lint-php-local lint-fix

lint-js:
	@echo "Linting JavaScript..."
	npm run lint:js

lint-css:
	@echo "Linting CSS..."
	npm run lint:css

# For CI environment (requires PHP installed)
lint-php:
	@echo "Linting PHP..."
	php vendor/bin/phpcs

# For local environment (uses wp-env)
lint-php-local:
	@echo "Linting PHP (via wp-env)..."
	$(WP_ENV) start
	$(WP_ENV_RUN) $(CONTAINER_VENDOR_BIN)/phpcs --standard=WordPress $(CONTAINER_PLUGIN_PATH)/includes/ $(CONTAINER_PLUGIN_PATH)/wwi-blogcard.php

lint: lint-js lint-css lint-php
	@echo "All linters passed!"

lint-fix:
	@echo "Fixing lint issues..."
	npm run format
	php vendor/bin/phpcbf || true
	@echo "Lint fixes applied!"

# ==============================================================================
# Testing
# ==============================================================================

.PHONY: test test-e2e

test: composer-install
	@echo "Running PHP tests..."
	docker compose run --rm php composer test
	@echo "Tests completed!"

test-e2e:
	npm run test:e2e

# ==============================================================================
# Build
# ==============================================================================

.PHONY: build

build:
	npm run build

# ==============================================================================
# Internal Targets (prefixed with _)
# ==============================================================================

.PHONY: _prepare-plugin-dir

# Prepare plugin directory for packaging and plugin-check
# Note: Assumes build/ directory already exists
_prepare-plugin-dir:
	rm -rf plugin-check-dir
	mkdir -p plugin-check-dir/$(PLUGIN_NAME)/languages
	cp wwi-blogcard.php readme.txt plugin-check-dir/$(PLUGIN_NAME)/
	cp -r includes plugin-check-dir/$(PLUGIN_NAME)/
	cp -r build plugin-check-dir/$(PLUGIN_NAME)/
	find languages/ -type f ! -name ".*" -exec cp {} plugin-check-dir/$(PLUGIN_NAME)/languages/ \; 2>/dev/null || true

# ==============================================================================
# Package
# ==============================================================================

.PHONY: package

package: build _prepare-plugin-dir
	@echo "Creating plugin package..."
	rm -rf dist/
	mkdir -p dist
	cd plugin-check-dir && zip -r ../dist/$(PLUGIN_NAME).zip $(PLUGIN_NAME) -x "*.git*" -x "*.DS_Store"
	rm -rf plugin-check-dir
	@echo "Package created: dist/$(PLUGIN_NAME).zip"

# ==============================================================================
# Clean
# ==============================================================================

.PHONY: clean

clean:
	rm -rf build/
	rm -rf node_modules/
	rm -rf vendor/
	rm -rf plugin-check-dir/
	rm -rf dist/

# ==============================================================================
# Plugin Check
# ==============================================================================

.PHONY: plugin-check plugin-check-dist

# Plugin Check (dev environment - includes all files)
plugin-check: build _prepare-plugin-dir
	@echo "Running WordPress Plugin Check..."
	$(WP_ENV) start
	$(WP_ENV_RUN) wp plugin check $(CONTAINER_PLUGIN_PATH)/plugin-check-dir/$(PLUGIN_NAME)
	rm -rf plugin-check-dir

# Plugin Check on distribution package (only production files)
plugin-check-dist: package
	@echo "Running WordPress Plugin Check on distribution package..."
	@rm -rf plugin-check-dist-dir
	@mkdir -p plugin-check-dist-dir
	@unzip -q dist/$(PLUGIN_NAME).zip -d plugin-check-dist-dir
	$(WP_ENV) start
	$(WP_ENV_RUN) cp -r $(CONTAINER_PLUGIN_PATH)/plugin-check-dist-dir/$(PLUGIN_NAME) /var/www/html/wp-content/plugins/$(PLUGIN_NAME)-dist-check
	$(WP_ENV_RUN) wp plugin check $(PLUGIN_NAME)-dist-check --slug=$(PLUGIN_NAME) --fields=type,code,message --format=table
	$(WP_ENV_RUN) rm -rf /var/www/html/wp-content/plugins/$(PLUGIN_NAME)-dist-check
	@rm -rf plugin-check-dist-dir
	@echo "Plugin Check completed!"

# ==============================================================================
# Review Check
# ==============================================================================

.PHONY: review-check

# Comprehensive check for WordPress.org submission (local environment)
review-check:
	@echo "=========================================="
	@echo "WordPress.org Review Submission Checklist"
	@echo "=========================================="
	@echo ""
	@echo "[1/5] Running JavaScript linter..."
	$(MAKE) lint-js
	@echo ""
	@echo "[2/5] Running CSS linter..."
	$(MAKE) lint-css
	@echo ""
	@echo "[3/5] Running PHP CodeSniffer..."
	$(MAKE) lint-php-local
	@echo ""
	@echo "[4/5] Running E2E tests..."
	$(MAKE) test-e2e
	@echo ""
	@echo "[5/5] Building and checking distribution package..."
	$(MAKE) plugin-check-dist
	@echo ""
	@echo "=========================================="
	@echo "All checks passed!"
	@echo "=========================================="
	@echo ""
	@echo "Next steps:"
	@echo "  1. Upload dist/$(PLUGIN_NAME).zip to WordPress.org"
	@echo "  2. Reply to the review email"
	@echo ""
