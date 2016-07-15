.PHONY: install clean test

install:
	composer install

clean:
	vendor/bin/php-cs-fixer fix src/

test:
	vendor/bin/phpunit
