<?php

namespace Drupal\Tests\oe_webtools_analytics\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the Webtools Analytics module.
 */
class WebtoolsAnalyticsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['oe_webtools_analytics'];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'minimal';

  /**
   * Test the output of the example page.
   */
  public function testWebtoolsAnalytics() {
    // Test that the main page for the example is accessible.
    $this->drupalGet('/');
    $this->assertSession()->statusCodeEquals(200);
  }

}
