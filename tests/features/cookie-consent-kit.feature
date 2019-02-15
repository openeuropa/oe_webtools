@api
Feature: Cookie consent kit.
  In order to show usage of cookie consent kit on the website
  As a anonymous user
  I want to be able to see usage of CCK for remote videos.

  @remote-video @cleanup:media
  Scenario: Remote videos should use cookie consent kit service.
    Given I visiting remote video entity page:
      | url                                         | title                  |
      | https://www.youtube.com/watch?v=1-g73ty9v04 | Energy, let's save it! |
    Then I should see the oEmbed video iframe with cookie consent kit usage
