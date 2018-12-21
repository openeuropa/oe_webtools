@api
Feature: Webtools LACO Widget
  In order to provide LACO
  As the site manager
  I need to be able to configure LACO widget
  And LACO widget works as expected

  Background:
    Given I am logged in as a user with the "administer site configuration" permission

  @BackupLacoConfigs
  Scenario: Create Webtools Analytics Rule
    Given I am on "admin/config/regional/oe_webtools_laco_widget"
    Then I should see "Webtools Laco Widget settings"
    And I should not see "header-test"
    And I should not see "footer-test"
    Then I fill in "Include" with "header-test"
    Then I fill in "Exclude" with "footer-test"
    And I press "Save configuration"
    Then I should see the message "The configuration options have been saved."
    And the "Include" field should contain "header-test"
    And the "Exclude" field should contain "footer-test"
