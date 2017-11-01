# features/wordpress/networkAdminUsers.feature
Feature: As a super admin I should be able to load Network Admin Users page

 @javascript
  Scenario: Network Admin Users page loads successfully
    Given I am logged in as "testuser1"
    Then I should visit "wp-admin/network/users.php"
    Then "div.wrap > h1" element has value "Users"
    Then "div.wrap > h1 > a" element has value "Add New"
    Then "form#form-user-list" element exists
    Then "table.wp-list-table.widefat.fixed.striped.users-network" element exists
    Then "th#username" element has value "Username"
    Then "th#name" element has value "Name"
    Then "th#email" element has value "Email"
    Then "th#registered" element has value "Registered"
    Then "th#blogs" element has value "Sites"
    Then "tbody#the-list" element exists
    Then "input#all-user-search-input" element exists
    Then "input#search-submit" element exists
    Then I log out