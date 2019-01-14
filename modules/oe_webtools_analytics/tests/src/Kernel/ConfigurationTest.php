<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics\Kernel;

use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Class ConfigurationTest.
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
    'oe_webtools',
    'oe_webtools_analytics',
  ];

  /**
   * Test that the Webtools JavaScript library is correctly loaded on a page.
   */
  public function testLibraryLoading(): void {
    $config = \Drupal::configFactory()
      ->getEditable(AnalyticsEventInterface::CONFIG_NAME)
      ->set("siteID", "123")
      ->set("sitePath", "ec.europa.eu")
      ->set("instance", "testing");
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"instance":"testing"}</script>');

    // Test the cache invalidation.
    $config = \Drupal::configFactory()
      ->getEditable(AnalyticsEventInterface::CONFIG_NAME)
      ->set("siteID", "1234")
      ->set("sitePath", "ec.europa.eu")
      ->set("instance", "testing");
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"1234","sitePath":["ec.europa.eu"],"instance":"testing"}</script>');

    $this->drupalGet('not-existing-page');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"1234","sitePath":["ec.europa.eu"],"is404":true,"instance":"testing"}</script>');

    $this->drupalGet('admin');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"1234","sitePath":["ec.europa.eu"],"is403":true,"instance":"testing"}</script>');
  }

}
