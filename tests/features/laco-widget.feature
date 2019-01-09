@api
Feature: Webtools LACO Widget
  In order to provide LACO service
  As the site manager
  I need to be able to configure LACO widget

  Background:
    Given I am logged in as a user with the "administer webtools laco widget configuration" permission

  @BackupLacoConfigs
  Scenario: Configure Webtools Laco Widget settings
    Given I am on "admin/config/regional/oe_webtools_laco_widget"
    Then I should see "Webtools Laco Widget settings"
    And I should not see "header-test"
    And I should not see "footer-test"
    When I fill in "Include" with "header-test"
    And I fill in "Exclude" with "footer-test"
    And I select "other" from "Document"
    And I select "other" from "Page"
    And I select "dot" from "Icon"
    And I press "Save configuration"
    Then I should see the message "The configuration options have been saved."
    And the "Include" field should contain "header-test"
    And the "Exclude" field should contain "footer-test"
    And the "Document" field should contain "other"
    And the "Page" field should contain "other"
    And the "Icon" field should contain "dot"
