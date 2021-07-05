@api @install:oe_webtools_cookie_consent @BackupCookieConsentConfigs
Feature: Cookie Consent kit.
  In order to show usage of Cookie Consent kit on the website
  As a anonymous user
  I want to give an explicit confirmation for cookies in a website

  @remote-video @cleanup:media
  Scenario: Remote videos should use Cookie Consent kit service.
    # Check that the oEmbed video iframe with Cookie Consent.
    Given I am an anonymous user
    When I visit the remote video entity page:
      | url                                         | title                  | path         |
      | https://www.youtube.com/watch?v=1-g73ty9v04 | Energy, let's save it! | /media/test  |
    Then I should see the oEmbed video iframe with Cookie Consent

    # Change the configuration.
    Given I am logged in as a user with the "administer webtools cookie consent" permission
    When I am on "the Webtools Cookie Consent configuration page"
    Then I should see "Webtools Cookie Consent settings"
    And the "Enable CCK video banner for the supported video elements." checkbox should be checked

    When I uncheck "Enable CCK video banner for the supported video elements."
    And I press "Save configuration"
    Then I should see the message "The configuration options have been saved."
    And the "Enable CCK video banner for the supported video elements." checkbox should not be checked

    # Check that the oEmbed video iframe without Cookie Consent.
    When I am on "/media/test"
    Then I should not see the oEmbed video iframe with Cookie Consent
