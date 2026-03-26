.PHONY: help run test phpunit phpstan phpcs phpcbf lint-twig lint-yaml audit play

help:
	@echo "CardManager - Available commands:"
	@echo "  make run        - Start Symfony dev server"
	@echo "  make test       - Run all checks"
	@echo "  make phpunit    - Run unit tests"
	@echo "  make phpstan    - Static analysis"
	@echo "  make phpcs      - Code style check"
	@echo "  make phpcbf     - Auto-fix code style"
	@echo "  make lint-twig  - Lint Twig templates"
	@echo "  make lint-yaml  - Lint YAML config"
	@echo "  make audit      - Security audit"
	@echo "  make play       - Play card game"

run:
	symfony server:start

test: lint-twig lint-yaml phpcs phpstan phpunit

phpunit:
	vendor/bin/phpunit

phpstan:
	vendor/bin/phpstan analyse

phpcs:
	vendor/bin/phpcs

phpcbf:
	vendor/bin/phpcbf

lint-twig:
	php bin/console lint:twig templates/

lint-yaml:
	php bin/console lint:yaml config/

audit:
	composer audit

play:
	php bin/console card:play
