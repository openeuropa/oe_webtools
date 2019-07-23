@api
Feature: Webtools Global Banner
  In order to provide a global banner
  As privileged user
  I need to be able to configure the settings of global banner

  @backup-globan-settings
  Scenario: Privileged user can change Global Banner settings.
    Given I am logged in as a user with the "administer webtools globan, access administration pages" permission
    When I am on "the Webtools Globan configuration page"
    And I select "Yes - display flag" from "Display EU flag"
    And I select "Dark" from "Background theme"
    And I select "Yes - show link" from "See all EU Institutions and bodies"
    And I press "Save"
    And I log out
    And I am on homepage
    Then Webtools javascript loaded with globan option "111"

    When I am logged in as a user with the "administer webtools globan, access administration pages" permission
    And I am on "the Webtools Globan configuration page"
    And I select "No - hide flag" from "Display EU flag"
    And I select "Light" from "Background theme"
    And I select "No - hide link" from "See all EU Institutions and bodies"
    And I press "Save"
    And I log out
    And I am on homepage
    Then Webtools javascript loaded with globan option "000"

    When I am logged in as a user with the "administer webtools globan, access administration pages" permission
    And I am on "the Webtools Globan configuration page"
    And I select "English" from "Override page language"
    And I press "Save"
    And I log out
    And I am on homepage
    Then Webtools javascript loaded with globan option "000" and language option "en"
