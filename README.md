# Behat Tests using Mink Library

## Installation
* Open terminal and clone this repository:
```sh
$ git clone https://github.com/neelakansha85/behattests.git
$ cd behattests
```
* Install project dependencies using composer (Required only when running tests without Docker)
```sh
$ php composer.phar install
```

## How to Run tests locally without Docker
* Start selenium webdriver and chrome driver in a terminal window and keep it running.
```sh
$ cd behattests && java -Dwebdriver.chrome.driver="chromedriver" -jar selenium-server-standalone-3.4.0.jar
```
* Open new terminal window and execute behat tests.
```sh
$ cd behattests/tests && ../bin/behat --config=behat.local.yml
```
Note: You need Selenium webdriver running for the tests using @javascript session, the other tests run using headless Goutte driver.

## How to Run tests using Docker
* Start docker machine
* Run tests using run_tests.sh script
```
$ ./run_tests.sh -s <suite-name> -n <network> -o <optional-args>
```
* Valid Arguments for run_tests.sh 
  * -s | --suite: accepts valid suite-name for `behat --suite="<suit-name>"` option. If not set, it will run all tests for the given network
  * -n | --network: accepts valid network config files for `behat --config="<network-config-file>"`. Default value is set to dev in start.sh
  * -o | --optional-args: accepts any valid optional arguments for behat such as `behat <optional-args> --config="<network-config-file>"`. Ex: `-o "--tags=@javascript"`

