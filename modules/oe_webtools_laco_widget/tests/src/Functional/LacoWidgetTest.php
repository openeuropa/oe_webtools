<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_laco_widget\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Check if the Laco widget code is on the front page.
 */
class LacoWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_webtools_laco_widget',
  ];

  /**
   * Test that the Laco widget JSON script loads on the page.
   */
  public function testLacoScriptLoading():void {
    $this->drupalGet('<front>');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"service":"laco","include":"body","coverage":{"document":"any","page":"any"},"icon":"all","exclude":".nolaco, .more-link, .pager"}</script>');
  }

}
