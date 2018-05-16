@api
Feature: Check Analytics
  In order to check if the type attribute is set for the Piwik element.
  As a developer and or a site administrator
  I want to check Piwik is available.

  Background:
    Given these modules are enabled
      | modules                            |
      | oe_webtools_analytics              |
    And I am logged in as a user with the "administrator" role
#  @webtoolsSettings
  Scenario: Check if the Analytics script is embedded into the page correctly
#    Given I am on the homepage
#    Then I should be able to set the webtools analytics property "siteID" with the value "12345670"
    Given I set the configuration item "oe_webtools.analytics" with key "siteID" to "12345670"
#      | key        | values      |
#      | siteID     | 12345670    |
    When I go to "/"
    Then the response status code should be 200
    Then the analytics script should have the property "siteID" set to value "12345670"

  Scenario: Check if the Analytics script flags non existing pages
    Given I go to "falsepage"
    Then the Analytics script should contains the "is404" configuration setting equal to true

  Scenario Outline: Check if the Analytics script flags forbidden pages
    Given I am not logged in
    When I go to "<path>"
    Then the Analytics script should contains the "is403" configuration setting equal to true
    Examples:
      | path                        |
      | admin/config                |
      | admin/structure             |
      | node/add/article            |
