@install:oe_webtools_maps @static
Feature: Step definitions to check presence of maps
  In order to define user scenarios involving maps
  As a UI designer
  I need to be able to use step definitions that detect the presence of maps on the page

Scenario: Try out the different step definitions for detecting maps
  Given I am on "no_maps.html"
  Then I should not see a map on the page
  Then I should not see any maps on the page

  Given I am on "one_map.html"
  Then I should see a map centered on latitude 4.370375 and longitude 50.842156
  And I should see a map on the page
  And I should see 1 map on the page

  Given I am on "/two_maps.html"
  Then I should see a map centered on latitude 4.370375 and longitude 50.842156
  And I should see a map centered on latitude 2.34994 and longitude 48.85295
  And I should see a map on the page
  And I should see 2 maps on the page
