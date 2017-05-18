#!/bin/bash

set -e
echo 'Starting Test Framework'
echo '======================='

sleep 2;

echo 'Now Testing...'

if [ "$NETWORK" == "dev" ]; then
  echo 'Running tests on DEV'
  if [ -z "$SUITE" ]; then
  	behat $OPTIONAL_ARGS --config behat.dev.yml
  else
	behat --suite=$SUITE OPTIONAL_ARGS --config behat.dev.yml
  fi

elif [ "$NETWORK" == "qa" ]; then
  echo 'Running tests on QA'
  if [ -z "$SUITE"]; then
  	behat $OPTIONAL_ARGS --config behat.qa.yml
  else
  	behat --suite=$SUITE $OPTIONAL_ARGS --config behat.qa.yml
  fi 

elif [ "$NETWORK" == "prod" ]; then
  echo 'Running tests on PROD'
  if [ -z "$SUITE"]; then
  	behat $OPTIONAL_ARGS --config behat.yml
  else
  	behat --suite=$SUITE $OPTIONAL_ARGS --config behat.yml
  fi

else
  echo 'Environment not set. Running default...'
  echo "Running all tests on DEV"
  behat $OPTIONAL_ARGS --config behat.dev.yml
fi

echo 'Finished tests...'
