@api
Feature: Cookie consent kit.
  In order to show usage of cookie consent kit on the website
  As a anonymous user
  I want to be able to see the usage of CCK for remote videos.

  @remote-video @cleanup:media
  Scenario: Remote videos should use cookie consent kit service.
    Given I visit the remote video entity page:
      | url                                         | title                  |
      | https://www.youtube.com/watch?v=1-g73ty9v04 | Energy, let's save it! |
    Then I should see the oEmbed video iframe with cookie consent

  @javascript
  Scenario Outline: Accept cookies
    Given user:
      | Username | test_cck |
      | Password | test_cck |

    When I am an anonymous user
    And I am on the homepage
    Then I should see the text "This site uses cookies to offer you a better browsing experience. Find out more on how we use cookies and how you can change your settings." in the "Cookie consent banner"
    And I should see the link "I accept cookies" in the "Cookie consent banner"
    And I should see the link "I refuse cookies" in the "Cookie consent banner"

    When I click "I <link> cookies"
    Then I should not see the "Cookie consent banner" region

    # Logging in does not require re-sign in.
    When I am on the homepage
    And I click "Sign in"
    And I fill in "Email or username" with "test_cck"
    And I fill in "Password" with "test_cck"
    And I press "Sign in"
    Then I should not see the "Cookie consent banner" region

    Examples:
      | link   |
      | accept |
      | refuse |
