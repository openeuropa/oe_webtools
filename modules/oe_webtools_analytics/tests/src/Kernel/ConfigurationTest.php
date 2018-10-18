<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics\Kernel;

use Drupal\Core\Cache\Cache;
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
      ->set("sitePath", "ec.europa.eu");
    $config->save();

    foreach (Cache::getBins() as $service_id => $cache_backend) {
      if ('dynamic_page_cache' === $service_id || 'page' === $service_id) {
        $cache_backend->deleteAll();
      }
    }

    $this->drupalGet('<front>');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"]}</script>');

    $this->drupalGet('not-existing-page');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"is404":true}</script>');

    $this->drupalGet('admin');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"is403":true}</script>');
  }

}
