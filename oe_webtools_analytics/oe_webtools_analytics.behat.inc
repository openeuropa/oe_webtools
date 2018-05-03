<?php

/**
 * @file
 * Contains \OEParagraphsSubContext.
 */

declare(strict_types = 1);

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\oe_webtools_analytics\Utils\WebtoolsAnalyticsIndex;

/**
 * Behat step definitions for testing the OE Webtools Analytics module.
 */
class OEWebtoolsAnalyticsSubContext extends DrupalSubContextBase {

  /**
   * Setup demo site.
   *
   * @BeforeScenario @webtoolsAnalytics
   */
  public function setupWebtoolsAnalytics(BeforeScenarioScope $scope) {
    \Drupal::service('module_installer')->install(['oe_webtools_analytics']);
  }

  /**
   * Checking if the settings on the page match the value on config.
   *
   * @Then the rendered script should contains the :arg1 with the same value as the configuration set in settings file
   */
  public function theSiteIdRenderedOnThePageShouldBeTheSameAsInSettingsFile($siteIdLabel) {
    // Getting the current settings.
    $settingsList = $this->getAnalyticsSettingsFromPage();

    // The siteId from the settings.php file.
    $settingsSiteId = \Drupal::configFactory()->get('oe_webtools.analytics')->get(WebtoolsAnalyticsIndex::SITE_ID);

    // The rendered siteId.
    $onPageSiteId = $settingsList[$siteIdLabel];

    // Message to be displayed in case of failure.
    $failMsg = "The siteID on settings is $settingsSiteId which differ to the rendered siteID = $onPageSiteId";

    PHPUnit_Framework_Assert::assertEquals($onPageSiteId, $settingsSiteId, $failMsg);
  }

  /**
   * Set a new config value for siteID.
   *
   * @Then I set the SiteId with :arg1 in settings file
   */
  public function setSideIdInSettingsFile($siteIdValue) {
    \Drupal::configFactory()->getEditable('oe_webtools.analytics')
      ->set(WebtoolsAnalyticsIndex::SITE_ID, $siteIdValue)
      ->save();
  }

  /**
   * Check if is403 or is404 is true on 403 and 404 page accordingly.
   *
   * @Then the rendered script should contains the :arg1 configuration setting equal to true
   */
  public function theRenderedScriptShouldContainsTheConfigurationSettingEqualToTrue($setting_key) {
    $settingsList = $this->getAnalyticsSettingsFromPage();
    switch ($setting_key) {
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
