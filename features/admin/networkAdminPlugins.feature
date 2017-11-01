# features/networkAdminPlugins.feature
Feature: As a super admin I should be able to load Network Admin Plugins page
  
  @javascript
  Scenario: Network Admin Plugins page loads successfully
    Given I am logged in as "testuser1"
    Then I should visit "wp-admin/network/plugins.php"
    Then "div.wrap > h1" element has value "Plugins"
    Then "div.wrap > h1 > a" element has value "Add New"
    Then "form#bulk-action-form" element exists
    Then "table.wp-list-table.widefat.plugins" element exists
    Then "th#name" element has value "Plugin"
    Then "th#description" element has value "Description"
    Then "th#image" element has value "Image"
    Then "tbody#the-list" element exists
    Then "input#plugin-search-input" element exists
    Then I log out