check_cli_dependencies:
	@./scripts/check_dependencies.sh

config.ini:
	@./scripts/create_config.sh

vendor:
	@make check_cli_dependencies
	@composer.phar install

update_dependencies: check_cli_dependencies
	@./scripts/update_dependencies.sh

public/system/simplesaml: config.ini check_cli_dependencies
	@./scripts/install_simplesamlphp.php

install: vendor public/system/simplesaml config.ini
	@git submodule init
	@git submodule update
	@cd js-webdav-client
	@make dist.js
	@cd ..
	@rm -vf public/system/js/webdavlib.js
	@ln -vs "$(pwd)/js-webdav-client/dist.js" public/system/js/webdavlib.js

tests/config.ini:
	@echo "Supply configuration for the test environment"
	@./scripts/create_config.sh tests/

test: tests/config.ini vendor check_cli_dependencies
	@./scripts/run_server_unittests.sh

docs: public/system/phpdoc

public/system/phpdoc: vendor src/* views/*
	@rm -rf public/system/phpdoc 2>/dev/null || true
	@mkdir public/system/phpdoc 2>/dev/null
	@./vendor/bin/phpdoc.php
