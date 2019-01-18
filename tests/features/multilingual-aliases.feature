@api
Feature: Webtools Analytics multilingual aliases
  In order to identify separate sections of my website
  As the site manager
  I need to be able to create rules that allow to pair section names with regular expressions with supporting aliases

  @cleanup:webtools_analytics_rule @cleanup-aliases
  Scenario: Create Webtools Analytics Rule with supporting multilingual aliases
    Given I am logged in as a user with the "administer site configuration, access administration pages" permission
    And the Webtools Analytics configuration is set to use the id '123' and the site path 'sitePath'
    And the following languages are available:
      | languages |
      | en        |
      | fr        |
      | nl        |
    And aliases available for the path "/admin/config":
      | languages | url        |
      | en        | /news       |
      | fr        | /nouvelles  |
      | nl        | /nieuws     |

    And I am on "admin/structure/webtools_analytics_rule/add"
    And I fill in "Machine-readable name" with "multilingual"
    And I fill in "Section" with "multilingual"
    And I check the box "Match on path alias for site default language."
    And I fill in "Regex" with "/news/"
    Then I press "Save"
    # Check the rule applies
    When I am on "/admin/config"
    Then the page analytics json should contain the parameter "siteSection" with the value "multilingual"
    When I am on "/news"
    Then the page analytics json should contain the parameter "siteSection" with the value "multilingual"
    When I am on "/fr/nouvelles"
    Then the page analytics json should contain the parameter "siteSection" with the value "multilingual"
    When I am on "/nl/nieuws"
    Then the page analytics json should contain the parameter "siteSection" with the value "multilingual"
    When I am on "/admin"
    Then the page analytics json should not contain the parameter "siteSection"

  @cleanup:webtools_analytics_rule @cleanup-aliases
  Scenario: Make sure that Webtools Analytics rules work without checked "Match on path alias for site default language"
    Given I am logged in as a user with the "administer site configuration, access administration pages" permission
    And the Webtools Analytics configuration is set to use the id '123' and the site path 'sitePath'
    And the following languages are available:
      | languages |
      | en        |
      | fr        |
    And aliases available for the path "/admin/config":
      | languages | url         |
      | en        | /news       |
      | fr        | /nouvelles  |

    And I am on "admin/structure/webtools_analytics_rule/add"
    And I fill in "Machine-readable name" with "alias1"
    And I fill in "Section" with "alias1"
    And I fill in "Regex" with "/news/"
    Then I press "Save"
    When I am on "/admin/config"
    Then the page analytics json should not contain the parameter "siteSection"
    When I am on "/news"
    Then the page analytics json should contain the parameter "siteSection" with the value "alias1"

    When I am on "admin/structure/webtools_analytics_rule/add"
    And I fill in "Machine-readable name" with "alias2"
    And I fill in "Section" with "alias2"
    And I fill in "Regex" with "/nouvelles/"
    Then I press "Save"
    When I am on "fr/nouvelles"
    Then the page analytics json should contain the parameter "siteSection" with the value "alias2"

