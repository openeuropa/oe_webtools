<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics_rules\Functional;

use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests for defining site sections using regular expressions.
 *
 * @group oe_webtools_analytics
 */
class SectionRulesTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_webtools',
    'oe_webtools_analytics',
    'oe_webtools_analytics_rules',
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

    $this->container->get('entity_type.manager')
      ->getStorage('webtools_analytics_rule')
      ->create(['id' => 'id1', 'section' => 'section1', 'regex' => '/admin/'])
      ->save();

    $this->drupalGet('<front>');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"]}</script>');

    $this->drupalGet('admin');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"siteSection":"section1","is403":true}</script>');

  }

}
