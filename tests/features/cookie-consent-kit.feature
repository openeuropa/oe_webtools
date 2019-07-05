@api
Feature: Cookie consent kit.
  In order to show usage of cookie consent kit on the website
  As a anonymous user
  I need to be able to configure the settings
  I want to be able to see the usage of CCK for remote videos.
  I want to be able to see that the Cookie consent banner Js is loaded.

  @BackupCookieConsentConfigs
  Scenario: Create Webtools Analytics settings
    Given I am logged in as a user with the "administer webtools cookie consent" permission
    When I am on "the Webtools Cookie Consent configuration page"
    Then I should see "Webtools Cookie Consent settings"
    And the "Enable Webtools Cookie Consent Kit." checkbox should be checked
    When I uncheck "Enable Webtools Cookie Consent Kit."
    And I press "Save configuration"
    Then I should see the message "The configuration options have been saved."
    And the "Enable Webtools Cookie Consent Kit." checkbox should not be checked

  @remote-video @cleanup:media
  Scenario: Remote videos should use cookie consent kit service.
    When I visit the remote video entity page:
      | url                                         | title                  |
      | https://www.youtube.com/watch?v=1-g73ty9v04 | Energy, let's save it! |
    Then I should see the oEmbed video iframe with cookie consent

  Scenario: Check CCK javascript is loaded.
    When I am on homepage
    Then the CCK javascript is loaded on the head section of the page
