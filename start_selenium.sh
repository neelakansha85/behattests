#!/bin/bash
set -e

# start_selenium.sh -s chrome=3

setGlobals() {
    readonly HUB_DOCKER_COMPOSE_FILE='docker-compose-hub.yml'
}

parseArgs() {
    while [ "$1" != "" ]; do
        case $1 in
          -s=* | --scale=*)
            SCALE="${1#*=}"
            ;;
          -s | --scale)
            SCALE=$2
            shift
            ;;
          -o=* | --optional-args=*)
            OPTIONAL_ARGS="${1#*=}"
            ;;
          -o | --optional-args)
            if [ ! -z $2 ]; then
                OPTIONAL_ARGS=$2
                shift
            fi
            ;;
          * )
            break 
            ;;
        esac
        shift
    done
}

startSelenium() {
    setGlobals
    parseArgs $@

    status=0

    echo "Starting Selenium Grid and Browser containers"
    echo "============================================"

    set +e
    if [ -z ${OPTIONAL_ARGS} ] || [ ${OPTIONAL_ARGS} == 'null' ]; then
        if [ -z $SCALE ]; then
            docker-compose -f $HUB_DOCKER_COMPOSE_FILE up -d
            status=$?
        else
            docker-compose -f $HUB_DOCKER_COMPOSE_FILE up -d --scale ${SCALE}
            status=$?
        fi
    else 
        if [ -z $SCALE ]; then
            docker-compose -f $HUB_DOCKER_COMPOSE_FILE up -d ${OPTIONAL_ARGS}
            status=$?
        else
            docker-compose -f $HUB_DOCKER_COMPOSE_FILE up -d --scale ${SCALE} ${OPTIONAL_ARGS}
            status=$?
        fi
    fi
    set -e

    exit $status
}

startSelenium $@