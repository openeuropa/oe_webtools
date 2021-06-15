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
    And I select "Yes" from "Sticky"
    And I press "Save"
    And I log out
    And I am on homepage
    Then the page should have globan json snippet '{"utility":"globan","theme":"dark","logo":true,"link":true,"mode":true}'
    When I am logged in as a user with the "administer webtools globan, access administration pages" permission
    And I am on "the Webtools Globan configuration page"
    And I select "No" from "Display the EU flag"
    And I select "Light" from "Background theme"
    And I select "No" from "Link to all EU Institutions and bodies"
    And I select "No" from "Sticky"
    And I press "Save"
    And I log out
    And I am on homepage
    Then the page should have globan json snippet '{"utility":"globan","theme":"light","logo":false,"link":false,"mode":false}'

    When I am logged in as a user with the "administer webtools globan, access administration pages" permission
    And I am on "the Webtools Globan configuration page"
    And I select "English" from "Override page language"
    And I fill in "Z-index" with "41"
    And I press "Save"
    And I log out
    And I am on homepage
    Then the page should have globan json snippet '{"utility":"globan","theme":"light","logo":false,"link":false,"mode":false,"lang":"en","zindex":41}'
