@api
Feature: Cookie consent kit.
  In order to show usage of cookie consent kit on the website
  As a anonymous user
  I want to be able to see the usage of CCK for remote videos.

  @remote-video @cleanup:media
  Scenario: Remote videos should use cookie consent kit service.
    When I visit the remote video entity page:
      | url                                         | title                  |
      | https://www.youtube.com/watch?v=1-g73ty9v04 | Energy, let's save it! |
    Then I should see the oEmbed video iframe with cookie consent

  Scenario: Check CCK javascript is loaded.
    When I am on homepage
    Then the CCK javascript is loaded on the head section of the page
