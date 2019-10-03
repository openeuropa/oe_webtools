@api @install:oe_webtools_globan
Feature: Webtools Global Banner
  In order to provide a global banner
  As privileged user
  I need to be able to configure the settings of global banner

  @backup-globan-settings
  Scenario: Privileged user can change Global Banner settings.
    Given I am logged in as a user with the "administer webtools globan, access administration pages" permission
    When I am on "the Webtools Globan configuration page"
    And I select "Yes" from "Display the EU flag"
    And I select "Dark" from "Background theme"
    And I select "Yes" from "Link to all EU Institutions and bodies"
    And I press "Save"
    And I log out
    And I am on homepage
    Then the Webtools javascript is loaded with the globan options "111"

    When I am logged in as a user with the "administer webtools globan, access administration pages" permission
    And I am on "the Webtools Globan configuration page"
    And I select "No" from "Display the EU flag"
    And I select "Light" from "Background theme"
    And I select "No" from "Link to all EU Institutions and bodies"
    And I press "Save"
    And I log out
    And I am on homepage
    Then the Webtools javascript is loaded with the globan options "000"

    When I am logged in as a user with the "administer webtools globan, access administration pages" permission
    And I am on "the Webtools Globan configuration page"
    And I select "English" from "Override page language"
    And I press "Save"
    And I log out
    And I am on homepage
    Then the Webtools javascript is loaded with the globan options "000" and language "en"
