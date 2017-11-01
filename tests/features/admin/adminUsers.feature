# features/wordpress/adminUsers.feature
Feature: As a admin I should be able to load the Users page on site

  @javascript
  Scenario: Load Users Page correctly
    Given I am logged in as "testuser1"
    Then I should visit "testbehat/wp-admin/users.php"
    Then "div.wrap > h1" element has value "Users"
    Then "div.wrap > h1 > a" element has value "Add New"
    Then "div.wrap > form" element exists
    Then "table.wp-list-table.widefat.fixed.striped.users" element exists
    Then "th#username" element has value "Username"
    Then "th#name" element has value "Name"
    Then "th#email" element has value "Email"
    Then "th#role" element has value "Role"
    Then "th#posts" element has value "Posts"
    Then "tbody#the-list" element exists
    Then "input#user-search-input" element exists
    Then "input#search-submit" element exists
    Then I log out
    