@javascript
Feature: Subsites load correctly

  Scenario: subsite1 loads correctly
    Given I am logged in as "testuser1"
    Given I should visit "subsite1"
    Then I should see "This is my Test Site 1"
    Then I log out

Scenario: subsite2 loads correctly
    Given I am logged in as "testuser1"
    Given I should visit "subsite2"
    Then I should see "Just another wordpress site"
    Then I log out

  Scenario: subsite3 loads correctly
    Given I am logged in as "testuser1"
    Given I should visit "subsite3"
    Then I should see "Welcome to my Site"
    Then I log out
