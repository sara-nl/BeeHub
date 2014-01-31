list:
	@echo "Usage: make {target}"
	@echo "Most common targets:"
	@echo " - list; show this list"
	@echo " - install; installs BeeHub. Run only when installing BeeHub for the first time on a new server!"
	@echo " - docs; create the API reference documentation for all server side code"
	@echo " - update_dependencies; update all dependencies. Harmless to run, and should be done every once in a while to keep everything up-2-date"
	@echo " - test; run server tests"

install: vendor public/system/simplesaml config.ini init_submodules
	@echo "Make sure your Apache webserver is configured as follows:"
	@echo " - Use $(pwd)public/ as document root"
	@echo " - \"AccessFileName .htaccess\" and \"AllowOverride All\" for the document root, or copy the directives in $(pwd)public/.htaccess into the Directory section of the central Apache configuration"
	@echo " - Have at least the following modules installed:"
	@echo "   * mod_rewrite"
	@echo "   * mod_ssl"
	@echo "   * php 5.3 or higher"
	@echo " - Listen for HTTP connections (preferably on port 80)"
	@echo " - Listen for HTTPS connections (preferably on port 443, but always 363 ports after the HTTP port)"
	@echo " - Apache has write access to the data directory and all subdirectories"
	@echo -ne "\nTo finish the installation, please use your browser to visit the website your Apache webserver is configured to respond to.\n"

docs: public/system/phpdoc

update_dependencies: check_cli_dependencies public/system/js/webdavlib.js
	@composer.phar update

test: tests/config.ini vendor check_cli_dependencies
	@./scripts/run_server_unittests.sh

check_cli_dependencies:
	@./scripts/check_dependencies.sh

config.ini:
	@./scripts/create_config.sh

vendor:
	@make check_cli_dependencies ;\
	composer.phar install

public/system/simplesaml: config.ini check_cli_dependencies
	@./scripts/install_simplesamlphp.php

init_submodules:
	@git submodule init ;\
	git submodule update ;\
	make public/system/js/webdavlib.js

public/system/js/webdavlib.js:
	@cd js-webdav-client ;\
	make dist.js ;\
	cd .. ;\
	rm -vf public/system/js/webdavlib.js ;\
	ln -vs "$(pwd)/js-webdav-client/dist.js" public/system/js/webdavlib.js

tests/config.ini:
	@echo "Supply configuration for the test environment"
	@./scripts/create_config.sh tests/

public/system/phpdoc: vendor src/* views/*
	@rm -rf public/system/phpdoc 2>/dev/null || true
	@mkdir public/system/phpdoc 2>/dev/null ;\
	./vendor/bin/phpdoc.php
