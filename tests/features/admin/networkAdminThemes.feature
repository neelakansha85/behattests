# features/wordpress/networkAdminThemes.feature
Feature: As a super admin I should be able to load Network Admin Themes page

 @javascript
  Scenario: Network Admin Themes page loads successfully
    Given I am logged in as "testuser1"
    Then I should visit "wp-admin/network/themes.php"
    Then "div.wrap > h1" element has value "Themes"
    Then "div.wrap > h1 > a" element has value "Add New"
    Then "form#bulk-action-form" element exists
    Then "table.wp-list-table.widefat.plugins" element exists
    Then "th#name" element has value "Theme"
    Then "th#description" element has value "Description"
    Then "th#image" element has value "Custom Image"
    Then "tbody#the-list" element exists
    Then "input#theme-search-input" element exists
    Then "input#search-submit" element exists
    Then I log out