<?php

/**
 * @file
 * Contains \OEParagraphsSubContext.
 */

declare(strict_types = 1);

use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\oe_webtools_analytics\Utils\WebtoolsAnalyticsIndex;

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
   * @Then I should be able to set the property :arg1 with the value :arg2
   */
  public function iShouldBeAbleToSetThePropertyWithTheValue(string $property, string $value) : void {
    \Drupal::configFactory()->getEditable('oe_webtools.analytics')
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
    $settingsList = $this->getAnalyticsSettingsFromPage();

    // The rendered siteId.
    $onPageSiteId = $settingsList[$property];

    // Message to be displayed in case of failure.
    $failMsg = "The siteID on settings is $value which differ to the rendered siteID = $onPageSiteId";

    PHPUnit_Framework_Assert::assertEquals($onPageSiteId, $value, $failMsg);
  }

  /**
   * Check if is403 or is404 is true on 403 and 404 page accordingly.
   *
   * @param string $property
   *   The property label, eg.: siteID, sitePath, is403, is404.
   *
   * @see Drupal\oe_webtools_analytics\Utils\WebtoolsAnalyticsIndex
   *
   * @Then the Analytics script should contains the :arg1 configuration setting equal to true
   */
  public function theAnalyticsScriptShouldContainsTheConfigurationSettingEqualToTrue($property) : void {
    $settingsList = $this->getAnalyticsSettingsFromPage();
    switch ($property) {
      case 'is403':
        PHPUnit_Framework_Assert::assertTrue($settingsList[WebtoolsAnalyticsIndex::IS403]);
        break;

      case 'is404':
        PHPUnit_Framework_Assert::assertTrue($settingsList[WebtoolsAnalyticsIndex::IS404]);
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

    $valid_settings = [
      'siteID',
      'sitePath',
      'utility',
    ];

    foreach ($valid_settings as $setting) {
      if (!isset($settings_list[$setting])) {
        throw new \RuntimeException("The configuration is missing, please check if the entry \"config['oe_webtools.analytics']['site_id']\" within settings.php exists.");
      }
    }

    return $settings_list;
  }

}
