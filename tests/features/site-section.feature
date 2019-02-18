@api
Feature: Webtools Analytics Site Section
  In order to identify separate sections of my website
  As the site manager
  I need to be able to create rules that allow to pair section names with regular expressions

  Background:
    Given I am logged in as a user with the "administer site configuration" permission
    And the Webtools Analytics configuration is set to use the id '123' and the site path 'sitePath'

  Scenario: Create Webtools Analytics Rule
    Given I am on "the Webtools Analytics rule creation page"
    And I fill in "Machine-readable name" with "rule1"
    And I fill in "Section" with "examplesection"
    And I fill in "Regex" with "|^/custompath/?$|"
    When I press "Save"
    Then I should be on "the Webtools Analytics rule page"
    Then I should see "examplesection"
    # Check the rule applies
    Given I am on "custompath"
    Then the page analytics json should contain the parameter "siteSection" with the value "examplesection"

  Scenario: Delete Webtools Analytics Rule
    Given I am on "the Webtools Analytics rule page"
    And I click "Delete" in the "examplesection" row
    And I press "Delete"
    Then I should be on "the Webtools Analytics rule page"
    Then I should not see "examplesection"
    # Check the rule doesnt apply
    Given I am on "custompath"
    Then the page analytics json should not contain the parameter "siteSection"
