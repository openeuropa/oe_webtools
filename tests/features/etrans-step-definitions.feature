@static
Feature: Step definitions to check presence of eTrans buttons
  In order to define user scenarios involving eTrans elements
  As a UI designer
  I need to be able to use step definitions that detect the presence of eTrans elements on the page

Scenario: Try out the different step definitions for detecting eTrans elements
  Given I am on "no_etrans.html"
  Then I should not see the Webtools eTrans button
  And I should not see the Webtools eTrans icon
  And I should not see the Webtools eTrans link
  And I should not see any Webtools eTrans elements

  Given I am on "etrans_button.html"
  Then I should see the Webtools eTrans button
  And I should see a Webtools eTrans element
  But I should not see the Webtools eTrans icon
  And I should not see the Webtools eTrans link

  Given I am on "etrans_icon.html"
  Then I should see the Webtools eTrans icon
  And I should see a Webtools eTrans element
  But I should not see the Webtools eTrans button
  And I should not see the Webtools eTrans link

  Given I am on "etrans_link.html"
  Then I should see the Webtools eTrans link
  And I should see a Webtools eTrans element
  But I should not see the Webtools eTrans button
  And I should not see the Webtools eTrans icon
