@api @webtoolsAnalytics
Feature: Check Piwik
  In order to check if the type attribute is set for the Piwik element.
  As a developer and or a site administrator
  I want to check Piwik is available.

  Background:
    Given these modules are enabled
      | modules                            |
      | oe_webtools_analytics              |
    And I am logged in as a user with the "administrator" role

  Scenario: Check if the PIWIK script is embedded into the page correctly
    Given I am on the homepage
    Then the response status code should be 200
    Then the rendered script should contains the "siteID" with the same value as the configuration set in settings file

  Scenario: Check if the PIWIK script flags non existing pages
    Given I go to "falsepage"
    Then the rendered script should contains the "is404" configuration setting equal to true

  Scenario Outline: Check if the PIWIK script flags forbidden pages
    Given I am not logged in
    When I go to "<path>"
    Then the rendered script should contains the "is403" configuration setting equal to true
    Examples:
      | path                        |
      | admin/config                |
      | admin/structure             |
      | node/add/article            |
