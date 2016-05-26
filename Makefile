.PHONY: all init clean

all: init

init: composer.phar
	php composer.phar install # install "dependencies"

composer.phar:
	php installer # install Composer

clean:
	rm composer.phar
	rm composer.lock
	rm -r vendor