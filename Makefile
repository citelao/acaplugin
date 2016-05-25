.PHONY: all init

all: init

init:
	php installer # install Composer
	php composer.phar install # install "dependencies"