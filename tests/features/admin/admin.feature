@javascript
Feature: Admin Dashboard loads successfully

  Scenario: Wordpress admin works
    Given I am logged in as "testuser1"
    Then I log out
    Then I should see "You are now logged out."