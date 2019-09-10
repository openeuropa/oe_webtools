<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics\Functional;

use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the configured settings are correctly output in the page.
 *
 * @group oe_webtools_analytics
 */
class ConfigurationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_webtools_analytics',
  ];

  /**
   * Tests if the configuration for the Webtools library is present on the page.
   */
  public function testLibraryLoading(): void {
    $config = \Drupal::configFactory()
      ->getEditable(AnalyticsEventInterface::CONFIG_NAME)
      ->set('siteID', '123')
      ->set('sitePath', 'ec.europa.eu')
      ->set('instance', 'testing');
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"instance":"testing"}</script>');

    $this->drupalGet('not-existing-page');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"is404":true,"instance":"testing"}</script>');

    $this->drupalGet('admin');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"is403":true,"instance":"testing"}</script>');

    // Test the cache invalidation.
    $config = \Drupal::configFactory()
      ->getEditable(AnalyticsEventInterface::CONFIG_NAME)
      ->set("siteID", "1234");
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"piwik","siteID":"1234","sitePath":["ec.europa.eu"],"instance":"testing"}</script>');
  }

}
