@javascript @shib @shibLogin
Feature: Shibboleth Login works

  Scenario: Shibboleth Login works and redirects correctly to wordpress admin
    Given I am logged in as "tst540" using shibboleth
    Then I log out