.PHONY: all init dist clean

all: init

init: composer.phar
	php composer.phar install # install "dependencies"

composer.phar:
	php installer # install Composer

dist: init
	rm -rf acac-features acac-features.zip
	mkdir acac-features
	cp -r acac-features.php index.php src vendor acac-features/
	zip -r acac-features.zip acac-features --exclude "*.DS_Store"
	rm -rf acac-features
	@echo "=== Built acac-features.zip ==="

clean:
	rm -f composer.phar acac-features.zip
	rm -rf vendor