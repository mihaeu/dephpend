NO_COLOR=\x1b[0m
OK_COLOR=\x1b[32;01m
ERROR_COLOR=\x1b[31;01m
WARN_COLOR=\x1b[33;01m

PHP=php
PHP_NO_INI=php -n
PHPUNIT=vendor/bin/phpunit

all: autoload tests testdox cov

autoload:
	composer dumpautoload

test:
	$(PHP) $(PHPUNIT) -c phpunit.xml.dist

feature:
	@$(PHP) $(PHPUNIT) tests/feature --testdox\
     | sed 's/\[x\]/$(OK_COLOR)$\[x]$(NO_COLOR)/' \
     | sed -r 's/(\[ \].+)/$(ERROR_COLOR)\1$(NO_COLOR)/' \
     | sed -r 's/(^[^ ].+)/$(WARN_COLOR)\1$(NO_COLOR)/'

testdox:
	@$(PHP_NO_INI) $(PHPUNIT) -c phpunit.xml.dist --testdox tests \
	 | sed 's/\[x\]/$(OK_COLOR)$\[x]$(NO_COLOR)/' \
	 | sed -r 's/(\[ \].+)/$(ERROR_COLOR)\1$(NO_COLOR)/' \
	 | sed -r 's/(^[^ ].+)/$(WARN_COLOR)\1$(NO_COLOR)/'

testdox-osx:
	@$(PHP_NO_INI) $(PHPUNIT) -c phpunit.xml.dist --testdox tests \
	 | sed 's/\[x\]/$(OK_COLOR)$\[x]$(NO_COLOR)/' \
	 | sed -E 's/(\[ \].+)/$(ERROR_COLOR)\1$(NO_COLOR)/' \
	 | sed -E 's/(^[^ ].+)/$(WARN_COLOR)\1$(NO_COLOR)/'

cov:
	@$(PHP) $(PHPUNIT) -c phpunit.xml.dist --coverage-text

style:
	@$(PHP_NO_INI) vendor/bin/php-cs-fixer fix --level=psr2 --verbose src
	@$(PHP_NO_INI) vendor/bin/php-cs-fixer fix --level=psr2 --verbose tests

phar:
	@composer update --no-dev
	@$(PHP) box.phar build
	@composer update

c: cov

d: testdox

s: style

t: test

f: feature
