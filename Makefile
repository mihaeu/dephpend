NO_COLOR=\x1b[0m
OK_COLOR=\x1b[32;01m
ERROR_COLOR=\x1b[31;01m
WARN_COLOR=\x1b[33;01m

PHP=php
PHP_NO_INI=php -n
PHPUNIT=vendor/bin/phpunit

all: autoload tests testdox cov

autoload:
	php composer.phar dumpautoload

t: test
test: unit feature

unit:
	$(PHP) $(PHPUNIT) -c phpunit.xml.dist

f: feature
feature:
	@$(PHP) $(PHPUNIT) tests/feature --testdox\
     | sed 's/\[x\]/$(OK_COLOR)$\[x]$(NO_COLOR)/' \
     | sed -r 's/(\[ \].+)/$(ERROR_COLOR)\1$(NO_COLOR)/' \
     | sed -r 's/(^[^ ].+)/$(WARN_COLOR)\1$(NO_COLOR)/'

d: testdox
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

c: cov
cov:
	@$(PHP) $(PHPUNIT) -c phpunit.xml.dist --coverage-text

s: style
style:
	@$(PHP_NO_INI) vendor/bin/php-cs-fixer fix --level=psr2 --verbose src
	@$(PHP_NO_INI) vendor/bin/php-cs-fixer fix --level=psr2 --verbose tests

phar:
	@php composer.phar update --no-dev
	@$(PHP) box.phar build
	@chmod +x build/dephpend.phar
	@php composer.phar update

pages:
	@pandoc README.md -o index.html --template template.html --variable pagetitle=dePHPend --toc --toc-depth 2 --variable title=dePHPend --variable date="`date`" --variable author="Michael Haeuslmann"
