# CardManager

A Symfony-based card game application.

## Tech Stack

- PHP ≥ 8.2
- Symfony 7.x
- Bootstrap 5

## Requirements

- PHP 8.2+
- Composer
- Symfony CLI

## Installation

```bash
composer install
```

## Usage

```bash
make run
```

## Available Commands

```bash
make test            # Run all checks
make phpunit         # Run unit tests
make phpstan        # Static analysis
make phpcs          # Code style check
make phpcbf         # Auto-fix code style
make lint-twig      # Lint Twig templates
make lint-yaml      # Lint YAML config
```

## Card Game

Play the card game:

```bash
php bin/command card:play
```

Or use the Makefile:

```bash
make play
```

## Development

```bash
make run          # Start dev server
make test        # Run all tests and linters
```
