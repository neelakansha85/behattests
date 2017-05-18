@javascript
Feature: Demo test for home page

  Scenario: Home page loads successfully
    Given I am on the homepage
    Then the url should match "/"
    Then the page title should be "Home"

