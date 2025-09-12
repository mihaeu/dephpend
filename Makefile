NO_COLOR=\x1b[0m
OK_COLOR=\x1b[32;01m
ERROR_COLOR=\x1b[31;01m
WARN_COLOR=\x1b[33;01m

PHP=php
PHPUNIT=vendor/bin/phpunit
BOX=vendor/bin/box
COMPOSER=composer

all: autoload test testdox cov

autoload:
	"$(COMPOSER)" dumpautoload

t: test
test: unit feature

unit:
	@$(PHPUNIT) -c phpunit.xml.dist

f: feature
feature:
	@$(PHPUNIT) tests/feature \
     | sed 's/\[x\]/$(OK_COLOR)$\[x]$(NO_COLOR)/' \
     | sed -r 's/(\[ \].+)/$(ERROR_COLOR)\1$(NO_COLOR)/' \
     | sed -r 's/(^[^ ].+)/$(WARN_COLOR)\1$(NO_COLOR)/'

d: testdox
testdox:
	@$(PHPUNIT) -c phpunit.xml.dist --testdox tests \
	 | sed 's/\[x\]/$(OK_COLOR)$\[x]$(NO_COLOR)/' \
	 | sed -r 's/(\[ \].+)/$(ERROR_COLOR)\1$(NO_COLOR)/' \
	 | sed -r 's/(^[^ ].+)/$(WARN_COLOR)\1$(NO_COLOR)/'

c: cov
cov:
	$(PHPUNIT) -c phpunit.xml.dist --coverage-text

s: style
style:
	$(PHP) vendor/bin/php-cs-fixer fix --rules=@PSR2 --verbose src
	$(PHP) vendor/bin/php-cs-fixer fix --rules=@PSR2 --verbose tests/feature
	$(PHP) vendor/bin/php-cs-fixer fix --rules=@PSR2 --verbose tests/unit

phar:
	$(COMPOSER) update --no-dev
	$(BOX) compile
	chmod +x build/dephpend.phar
	$(COMPOSER) update
