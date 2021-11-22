.PHONY: help clean test dev

ROOT_DIR 	   := $(abspath $(lastword $(MAKEFILE_LIST)))
PROJECT_DIR	 := $(notdir $(patsubst %/,%,$(dir $(ROOT_DIR))))
PROJECT 		 := $(lastword $(PROJECT_DIR))
VERSION_FILE 	= VERSION
VERSION			 	= `cat $(VERSION_FILE)`

RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
$(eval $(RUN_ARGS):;@:)

default: dev

help: ## Print all the available commands
	@echo "" \
	&& grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
	  awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}' \
	&& echo ""

clean: ## Cleans project by removing vendor folder and composer lock.
	@echo "Cleaning project..." \
	&& rm -rf vendor composer.lock coverage/html

test: ## Runs PHPUnit tests inside the test  directory
	@echo "Running tests..." \
	&& phpunit --coverage-html coverage/html tests

dev: ## Run project inn development development
	@echo "Running in development..." \
	&& php src/main.php

