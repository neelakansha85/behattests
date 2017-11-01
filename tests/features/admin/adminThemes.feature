# features/wordpress/adminThemes.feature
Feature: As an site admin I should be able to load the Themes page of the site

  @javascript
  Scenario: Admin Themes page loads
    Given I am logged in as "testuser1"
    Then I should visit "testbehat/wp-admin/themes.php?page=multisite-theme-manager.php"
    Then "div.wrap > h2" element has value "Themes"
    Then "div.wrap > div.theme-categories" element exists
    Then "input#theme-search-input" element exists
    Then "div.theme-browser" element exists
    Then "div.theme.active" element exists
    Then "div.theme.active > div.theme-screenshot" element exists
    Then "div.theme.active > h3.theme-name" element exists
    Then "div.theme.active > div.theme-actions" element exists
    Then I log out
    