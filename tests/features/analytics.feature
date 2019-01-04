@api
Feature: Webtools Analytics
  In order to provide analytics
  As the site manager
  I need to be able to configure the settings
  And Webtools Analytics works as expected

  Background:
    Given I am logged in as a user with the "administer site configuration" permission

  Scenario: Create Webtools Analytics settings
    Given I am on "admin/config/regional/oe_webtools_analytics"
    Then I should see "Webtools Analytics settings"
    When I fill in "Site ID" with "INFO"
    And I fill in "Site path" with "ec.europa.eu/info"
    And I fill in "Instance" with "ec.europa.eu"
    And I press "Save configuration"
    Then I should see the message "The configuration options have been saved."
    And the "Site ID" field should contain "INFO"
    And the "Site path" field should contain "ec.europa.eu/info"
    And the "Instance" field should contain "ec.europa.eu"
