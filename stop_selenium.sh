#!/bin/bash
set +e

# stop_selenium.sh

setGlobals() {
    readonly HUB_DOCKER_COMPOSE_FILE='docker-compose-hub.yml'
}

stopSelenium() {
	setGlobals

	echo "Stop Selenium Grid if running"
	echo "============================="

	docker-compose -f $HUB_DOCKER_COMPOSE_FILE kill
	docker-compose -f $HUB_DOCKER_COMPOSE_FILE rm -f
}

stopSelenium $@
