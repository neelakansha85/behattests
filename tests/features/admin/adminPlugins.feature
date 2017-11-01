# features/wordpress/adminPlugins.feature
Feature: As a admin I should be able to load the Admin Plugins page

  @javascript
  Scenario: Plugins page loads
    Given I am logged in as "testuser1"
    Then I should visit "testbehat/wp-admin/admin.php?page=pretty-plugins.php"
    Then "div.wrap > h2" element has value "Plugins"
    Then "div#current-theme" element exists
    Then "div.type.categories" element exists
    Then "div#availableplugins" element exists
    Then "div.available-theme.available-plugin" element exists
    Then "div.action-links" element exists
    Then "div.action-links > ul > li > a.activate-deactivate" element exists
    Then "input#theme-search-input" element exists
    Then I log out