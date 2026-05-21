<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_html2media\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the route to get the binary file is available.
 *
 * @group oe_webtools
 */
class HtmlToMediaTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_webtools_html2media',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test the route.
   */
  public function testInternalRoute() {
    // Use module permission.
    $user = $this->createUser([
      'use webtools html2media version',
    ]);
    $this->drupalLogin($user);
    $this->drupalGet('oe-webtools-html2media');
    $this->assertSession()->pageTextContains('Error: please provide the url of the page to convert');
  }

}
