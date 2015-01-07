list:
	@echo "Usage: make {target}"
	@echo "Most common targets:"
	@echo " - list; show this list"
	@echo " - install; installs BeeHub. Run only when installing BeeHub for the first time on a new server!"
	@echo " - docs; create the API reference documentation for all server side code"
	@echo " - update_dependencies; update all dependencies. Harmless to run, and should be done every once in a while to keep everything up-2-date"
	@echo " - test; run server tests"

install: vendor public/system/simplesaml config.ini
	@./scripts/webserver_install.sh

docs: public/system/phpdoc

update_dependencies: check_cli_dependencies
	@composer.phar update

test: tests/config.ini vendor check_cli_dependencies
	@./scripts/run_server_unittests.sh

check_cli_dependencies:
	@./scripts/check_dependencies.sh

config.ini:
	@./scripts/create_config.sh

vendor:
	@make check_cli_dependencies && \
	composer.phar install

public/system/simplesaml: config.ini check_cli_dependencies
	@./scripts/install_simplesamlphp.php

tests/config.ini:
	@echo "Supply configuration for the test environment"
	@./scripts/create_config.sh tests/

public/system/phpdoc: vendor src/* views/*
	@rm -rf public/system/phpdoc 2>/dev/null || true
	@mkdir public/system/phpdoc 2>/dev/null && \
	./vendor/bin/phpdoc.php
