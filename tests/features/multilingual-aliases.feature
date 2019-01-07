@api
Feature: Webtools Analytics multilingual aliases
  In order to identify separate sections of my website
  As the site manager
  I need to be able to create rules that allow to pair section names with regular expressions with supporting aliases

  Background:
    Given I am logged in as a user with the "administer site configuration" permission
    And the Webtools Analytics configuration is set to use the id '123' and the site path 'sitePath'
    And the following languages are available:
      | languages |
      | en        |
      | fr        |
      | nl        |
    And Aliases available for the path "/admin/config":
      | languages | url        |
      | en        | /news       |
      | fr        | /nouvelles  |
      | nl        | /nieuws     |

  Scenario: Create Webtools Analytics Rule with supporting multilingual aliases
    Given I am on "admin/structure/webtools_analytics_rule/add"
    And I fill in "Machine-readable name" with "multilingual"
    And I fill in "Section" with "multilingual"
    And I check the box "Is support multilingual aliases"
    And I fill in "Regex" with "/news/"
    When I press "Save"
    # Check the rule applies
    Given I am on "/admin/config"
    Then the page analytics json should contain the parameter "siteSection" with the value "multilingual"
    Given I am on "/news"
    Then the page analytics json should contain the parameter "siteSection" with the value "multilingual"
    Given I am on "/fr/nouvelles"
    Then the page analytics json should contain the parameter "siteSection" with the value "multilingual"
    Given I am on "/nl/nieuws"
    Then the page analytics json should contain the parameter "siteSection" with the value "multilingual"
    Given I am on "/admin"
    Then the page analytics json should not contain the parameter "siteSection"

