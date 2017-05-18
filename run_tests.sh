#!/bin/bash
set -e

# run_tests.sh -s admin -n dev -o "--tags=@label"

if [ ! $# -ge 2 ]; then
    echo "Please enter the correct arguments"
    echo "Usage: ./run_tests.sh -s <suite-name> -n <network> -o <optional-args>"
    exit 1
fi

while [ "$1" != "" ]; do
    case $1 in
      -s | --suite)
        suite=$2
        shift
        ;;
      -n | --network)
        network=$2
        shift
        ;;
      -o | --optional-args)
        optional_args=$2
        shift
        ;;
      * )
        break 
        ;;
    esac
    shift
done

# rebuild in case of any updates to Dockerfile
docker-compose build

docker-compose run -e SUITE="${suite}" -e NETWORK="${network}" -e OPTIONAL_ARGS="${optional_args}" behat

# chrome and hub containers will run indefinitely after the behat container exits so kill them
docker-compose kill chrome hub && docker-compose rm -f
