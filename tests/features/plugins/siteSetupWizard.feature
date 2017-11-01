# features/plugins/siteSetupWizard.feature
Feature: As a user I should be able to create new site using Site Setup Wizard Plugin.

  @javascript
  Scenario: Verify Site Setup Wizard Plugin is installed and Network Activated
    Given I am logged in as "testuser1"
    Then "Site Setup Wizard" plugin is installed
    Then "Site Setup Wizard" plugin is network activated
    Then I log out

  @javascript
  Scenario: Steps to create new site
    Given I am logged in as "testuser1"
    Then I should visit "create"
    Then I should not see "You must first log in to create new site."
    Then I click "div.ssw-xtra-block > a" if visible


    # Step1
    Then I press "Personal"
    Then I wait 2 sec

    # Step 2
    Then I fill in "ssw-admin-email" with "user2@yoursite.com"
    Then I select "teachinglearning" from "ssw-site-category"
    Then I fill in "ssw-site-address" with "testssw"
    Then I fill in "ssw-site-title" with "Test Site Setup Wizard"
    Then I select "3" from "site_privacy"
    Then I check "ssw-site-terms-input"
    Then I press "Next"
    Then I wait 10 sec

    # Step 3
    Then I select "twenty-seventeen" from "select_theme"
    Then I press "Next"
    Then I wait 5 sec

    # Step 4
    Then scroll to element with class "ssw-h4"
    When I check "iframe"
    When I check "TumblrWidget"
    Then I press "Finish"
    Then I wait 5 sec
    Then I should see "Your new site is now ready at"
    Then I should see "/testssw"

    # Log out
    Then I should visit "wp-admin"
    Then I log out

  Scenario: Verify if the test site testssw was created using the Site Setup Wizard
    Given I am logged in as "testuser1"

    # Check Privacy setting
    Then I should visit "testssw/wp-admin/options-reading.php"
    Then the "blog-norobots" checkbox should be checked

    # Check Site Title and site admin email
    Then I should visit "testssw/wp-admin/options-general.php"
    Then the "blogname" field should contain "Test Site Setup Wizard"
    Then the "new_admin_email" field should contain "user2@yoursite.com"

    # Check the plugins installed
    Then I should visit "testssw/wp-admin/plugins.php"
    Then "iframe" plugin is activated
    Then "Tumblr Widget" plugin is activated

    # Check site type and current theme
    Then I should visit "wp-admin/network/sites.php"
    Then I fill in "site-search-input" with "testssw"
    Then I press "Search Sites"
    Then I wait 2 sec
    Then I follow "testssw"
    Then I follow "site-settings"
    Then the "nsd_ssw_site_type" field should contain "Personal"
    Then the "current_theme" field should contain "Twenty Seventeen"

    # Log out
    Then I log out

  Scenario: Delete the testssw site
    Given I am logged in as "testuser1"
    Then I should visit "wp-admin/network/sites.php"
    Then I fill in "site-search-input" with "testssw"
    Then I press "Search Sites"
    Then I wait 2 sec
    Then I follow "Delete"
    Then I wait 2 sec
    Then I press "Confirm"
    Then I log out