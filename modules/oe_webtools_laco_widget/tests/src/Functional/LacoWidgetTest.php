<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics\Functional;

use Drupal\Tests\BrowserTestBase;

class LacoWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_webtools',
    'oe_webtools_laco_widget',
  ];

  /**
   * Test that the Laco widget JSON script loads on the page.
   */
  public function testLacoScriptLoading(): void {
    $this->drupalGet('<front>');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"service":"laco","include":"#page-wrapper","coverage":{"document":"any","page":"any"},"icon":"all","exclude":".nolaco, .more-link, .pager"}</script>');
  }

}