@api @BackupCookieConsentConfigs
Feature: Cookie consent kit.
  In order to show usage of cookie consent kit on the website
  As a anonymous user
  I want to give an explicit confirmation for cookies in a website

  Scenario: Create Webtools Cookie Consent settings
    # Check that CCK javascript is loaded for the anonymous user.
    Given I am an anonymous user
    When I am on homepage
    Then the CCK javascript is loaded on the head section of the page

    # Change the configuration.
    Given I am logged in as a user with the "administer webtools cookie consent" permission
    When I am on "the Webtools Cookie Consent configuration page"
    Then I should see "Webtools Cookie Consent settings"
    And the "Enable the CCK banner." checkbox should be checked

    When I uncheck "Enable the CCK banner."
    And I press "Save configuration"
    Then I should see the message "The configuration options have been saved."
    And the "Enable the CCK banner." checkbox should not be checked

    # Check that CCK javascript is not loaded for the anonymous user after change.
    Given I am an anonymous user
    When I am on homepage
    Then the CCK javascript is not loaded on the head section of the page

  @remote-video @cleanup:media
  Scenario: Remote videos should use cookie consent kit service.
    # Check that the oEmbed video iframe with cookie consent.
    Given I am an anonymous user
    When I visit the remote video entity page:
      | url                                         | title                  | path         |
      | https://www.youtube.com/watch?v=1-g73ty9v04 | Energy, let's save it! | /media/test  |
    Then I should see the oEmbed video iframe with cookie consent

    # Change the configuration.
    Given I am logged in as a user with the "administer webtools cookie consent" permission
    When I am on "the Webtools Cookie Consent configuration page"
    Then I should see "Webtools Cookie Consent settings"
    And the "Enable the override of Media oEmbed iframe." checkbox should be checked

    When I uncheck "Enable the override of Media oEmbed iframe."
    And I press "Save configuration"
    Then I should see the message "The configuration options have been saved."
    And the "Enable the override of Media oEmbed iframe." checkbox should not be checked

    # Check that the oEmbed video iframe without cookie consent.
    When I am on "/media/test"
    Then I should not see the oEmbed video iframe with cookie consent
