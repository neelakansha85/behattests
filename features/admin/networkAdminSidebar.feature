# features/networkAdminSidebar.feature
Feature: As a super admin I should be able to load Network Admin Sidebar

  @javascript
  Scenario: Network Admin Sidebar loads
    Given I am logged in as "testuser1"
    Then I should see "Dashboard"
    Then "div#adminmenuwrap" element exists
    Then "li#menu-dashboard" element exists
    Then "li#menu-settings" element exists
    Then "li#menu-users" element exists
    Then I log out