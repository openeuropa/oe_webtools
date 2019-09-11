@api @install:oe_webtools_analytics_rules
Feature: Webtools Analytics Site Section
  In order to identify separate sections of my website
  As the site manager
  I need to be able to create rules that allow to pair section names with regular expressions

  Background:
    Given I am logged in as a user with the "administer webtools analytics" permission
    And the Webtools Analytics configuration is set to use the id '123' and the site path 'sitePath'

  @cleanup:webtools_analytics_rule
  Scenario: Create Webtools Analytics Rule
    Given I am on "the Webtools Analytics rule creation page"
    And I fill in "Machine-readable name" with "rule1"
    And I fill in "Section" with "examplesection"
    And I fill in "Regular expression" with "|^/custompath/?$|"
    When I press "Save"
    Then I should be on "the Webtools Analytics rule page"
    And I should see "examplesection"
    # Check the rule applies.
    When I am on "custompath"
    Then the page analytics json should contain the parameter "siteSection" with the value "examplesection"

  @cleanup:webtools_analytics_rule
  Scenario: Delete Webtools Analytics Rule
    Given I am on "the Webtools Analytics rule creation page"
    And I fill in "Machine-readable name" with "rule1"
    And I fill in "Section" with "examplesection"
    And I fill in "Regular expression" with "|^/custompath/?$|"
    And I press "Save"
    When I am on "the Webtools Analytics rule page"
    And I click "Delete" in the "examplesection" row
    And I press "Delete"
    Then I should be on "the Webtools Analytics rule page"
    And I should not see "examplesection"
    # Check the rule doesn't apply.
    When I am on "custompath"
    Then the page analytics json should not contain the parameter "siteSection"

  @cleanup:webtools_analytics_rule
  Scenario: Make sure that Webtools Analytics Rules applies by priority
    Given I am on "the Webtools Analytics rule creation page"
    And I fill in "Machine-readable name" with "rule1"
    And I fill in "Section" with "examplesection1"
    And I fill in "Regular expression" with "|^/custompath|"
    When I press "Save"
    Then I should be on "the Webtools Analytics rule page"
    And I should see "examplesection1"
    # Check the rule applies.
    When I am on "custompath"
    Then the page analytics json should contain the parameter "siteSection" with the value "examplesection1"

    When I am on "the Webtools Analytics rule creation page"
    And I fill in "Machine-readable name" with "rule2"
    And I fill in "Section" with "examplesection2"
    And I fill in "Regular expression" with "|^/custompath/subpage|"
    And I press "Save"
    Then I should be on "the Webtools Analytics rule page"
    And I should see "examplesection2"
    # We still see applying of previous rule.
    When I am on "custompath/subpage"
    Then the page analytics json should contain the parameter "siteSection" with the value "examplesection1"
    # Re-order the rules to change their priority.
    When I am on "the Webtools Analytics rule page"
    And I select "-9" weight in the "examplesection1" row
    And I select "-10" weight in the "examplesection2" row
    And I press "Save"
    And I am on "custompath/subpage"
    Then the page analytics json should contain the parameter "siteSection" with the value "examplesection2"
