.PHONY: sniff test coverage

test: vendor/autoload.php
	vendor/bin/phpunit --verbose

vendor/autoload.php:
	composer install --no-interaction --prefer-dist