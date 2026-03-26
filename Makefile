.PHONY: help run test phpunit phpstan lint-twig lint-yaml audit play

help:
	@echo "CardManager - Available commands:"
	@echo "  make run        - Start Symfony dev server"
	@echo "  make test       - Run all checks"
	@echo "  make phpunit    - Run unit tests"
	@echo "  make phpstan    - Static analysis"
	@echo "  make lint-twig  - Lint Twig templates"
	@echo "  make lint-yaml  - Lint YAML config"
	@echo "  make audit      - Security audit"
	@echo "  make play       - Play card game"

run:
	symfony server:start

test: lint-twig lint-yaml phpunit

phpunit:
	vendor/bin/phpunit

lint-twig:
	php bin/console lint:twig templates/

lint-yaml:
	php bin/console lint:yaml config/

audit:
	composer audit

play:
	php bin/console card:play
