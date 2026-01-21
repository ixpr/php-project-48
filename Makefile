install:
	composer install

validate:
	composer validate

dump:
	composer dump-autoload

test:
	composer exec --verbose phpunit tests -- --testdox

coverage:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-text

coveragehtml:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-html ./
