.PHONY: dev dev-build dev-down dev-destroy lint lint-fix test test-e2e build package clean

# Development
dev:
	docker compose up -d

dev-build:
	docker compose up -d --build

dev-down:
	docker compose down

dev-destroy:
	docker compose down -v

# Linting
lint:
	@echo "🔍 Running all linters..."
	@echo "📜 Linting JavaScript..."
	npm run lint:js
	@echo "🎨 Linting CSS..."
	npm run lint:css
	@echo "🐘 Linting PHP..."
	composer phpcs
	@echo "✅ All linters passed!"

lint-fix:
	@echo "🔧 Fixing lint issues..."
	npm run format
	composer phpcbf || true
	@echo "✅ Lint fixes applied!"

# Testing
test:
	composer test

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
