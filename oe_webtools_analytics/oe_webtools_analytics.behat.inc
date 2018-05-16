<?php

/**
 * @file
 * Contains \OEWebtoolsAnalyticsSubContext.
 */

declare(strict_types = 1);

use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;

/**
 * Behat step definitions for testing the OE Webtools Analytics module.
 */
class OEWebtoolsAnalyticsSubContext extends DrupalSubContextBase {

  /**
   * Create the analytics settings on runtime.
   *
   * @param string $property
   *   The property label, eg.: siteID, sitePath, is403, is404.
   * @param string $value
   *   The actual property value.
   *
   * @Then I should be able to set the webtools analytics property :arg1 with the value :arg2
   */
  public function iShouldBeAbleToSetThePropertyWithTheValue(string $property, string $value) : void {
    \Drupal::configFactory()->getEditable(AnalyticsEventInterface::WEBTOOLS_ANALYTICS_SETTINGS)
      ->set($property, $value)
      ->save();
  }

  /**
   * Inspect whether or not the analytic's script has the right settings value.
   *
   * @param string $property
   *   The property label, eg.: siteID, sitePath, is403, is404.
   * @param string $value
   *   The actual property value.
   *
   * @Then the analytics script should have the property :arg1 set to value :arg2
   */
  public function theAnalyticsScriptShouldHaveThePropertySetToValue($property, $value) : void {

    // Getting the current settings.
    $settings_list = $this->getAnalyticsSettingsFromPage();

    // The rendered siteId.
    $on_page_siteId = $settings_list[$property];

    // Message to be displayed in case of failure.
    $failed_msg = "The siteID on settings is $value which differ to the rendered siteID = $on_page_siteId";

    PHPUnit_Framework_Assert::assertEquals($on_page_siteId, $value, $failed_msg);
  }

  /**
   * Check if is403 or is404 is true on 403 and 404 page accordingly.
   *
   * @param string $property
   *   The property label, eg.: siteID, sitePath, is403, is404.
   *
   * @see \Drupal\oe_webtools_analytics\AnalyticsEventInterface
   *
   * @Then the Analytics script should contains the :arg1 configuration setting equal to true
   */
  public function theAnalyticsScriptShouldContainsTheConfigurationSettingEqualToTrue($property) : void {
    $settingsList = $this->getAnalyticsSettingsFromPage();
    switch ($property) {
      case AnalyticsEventInterface::IS403:
        PHPUnit_Framework_Assert::assertTrue($settingsList[AnalyticsEventInterface::IS403]);
        break;

      case AnalyticsEventInterface::IS404:
        PHPUnit_Framework_Assert::assertTrue($settingsList[AnalyticsEventInterface::IS404]);
        break;

      default:
        break;
    }
  }

  /**
   * The list of webtools settings found in current page.
   *
   * @return array
   *   An array of  settings found in current page.
   */
  private function getAnalyticsSettingsFromPage() : array {
    $session = $this->getSession();
    // Inspect if the <script type="application/json">{"utility":"piwik","siteID":"5454544545454","sitePath":["localhost\/open_europa\/oe_webtools\/build\/"]}</script>.
    $elem = $session->getPage()->find('css', "script[type='application/json']");
    $settings_list = json_decode($elem->getText(), TRUE);

    $required_settings = [
      AnalyticsEventInterface::SITE_ID,
      AnalyticsEventInterface::SITE_PATH,
    ];

    foreach ($required_settings as $setting) {
      if (!isset($settings_list[$setting])) {
        throw new \RuntimeException("The configuration is missing, please check if the entry \"$setting\" exists within settings.php.");
      }
    }

    return $settings_list;
  }

  /**
   * Validate the property accordingly to the documentation.
   *
   * @param string $attributeName
   *   The nalytic attribute name.
   *
   * @return bool
   *   True or false.
   */
  private function isValidAttribute(string $attributeName) : boolean {
    $validAttributes = [
      AnalyticsEventInterface::IS403,
      AnalyticsEventInterface::IS404,
      AnalyticsEventInterface::SITE_ID,
      AnalyticsEventInterface::UTILITY,
      AnalyticsEventInterface::SITE_PATH,
      AnalyticsEventInterface::SITE_SECTION,
    ];

    return in_array($attributeName, $validAttributes);
  }

}
