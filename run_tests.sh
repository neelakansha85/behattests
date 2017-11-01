#!/bin/bash
set -e

# run_tests.sh -p dev -o "--tags=~@admin"

setGlobals() {
    readonly START_SELENIUM_FILE="start_selenium.sh"
    readonly STOP_SELENIUM_FILE="stop_selenium.sh"
}

parseArgs() {
    if [ ! $# -ge 1 ]; then
        echo "Please enter the correct arguments"
        echo "Usage: ./run_tests.sh -p dev -o \"--tags=@admin\""
        exit 1
    fi

    while [ "$1" != "" ]; do
        case $1 in
          -p=* | --profile=*)
            PROFILE="${1#*=}"
            ;;
          -p | --profile)
            PROFILE=$2
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
          --start-selenium)
            START_SELENIUM=true
            ;;
          --stop-selenium)
            STOP_SELENIUM=true
            ;;
          * )
            break 
            ;;
        esac
        shift
    done
}

startSelenium() {
    echo "Starting Selenium Grid and Browser containers in background"
    echo "==========================================================="
    nohup ./$START_SELENIUM_FILE &
    
    echo "Waiting for the selenium containers to be up and running"
    sleep 10
}

stopSelenium() {
    exec ./$STOP_SELENIUM_FILE
}

runTests() {
    setGlobals
    parseArgs $@

    if [ "${START_SELENIUM}" = true ]; then
        startSelenium
    fi

    status=0

    echo "Starting Behat Tests"
    echo "====================="
    echo "Running tests on $PROFILE"

    set +e
    if [ -z ${OPTIONAL_ARGS} ] || [ ${OPTIONAL_ARGS} == 'null' ]; then
        docker-compose run -e PROFILE="${PROFILE}" behat
        status=$?
    else 
        docker-compose run -e PROFILE="${PROFILE}" -e OPTIONAL_ARGS="${OPTIONAL_ARGS}" behat
        status=$?
    fi
    set -e

    if [ "${STOP_SELENIUM}" = true ]; then
        stopSelenium
    fi

    exit $status

}

runTests $@
