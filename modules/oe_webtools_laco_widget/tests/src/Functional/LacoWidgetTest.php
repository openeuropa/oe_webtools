<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_laco_widget\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_webtools\Traits\ContentAssertionTrait;

/**
 * Check if the Laco widget code is on the front page.
 */
class LacoWidgetTest extends BrowserTestBase {

  use ContentAssertionTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_webtools_laco_widget',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test that the Laco widget JSON script loads on the page.
   */
  public function testLacoScriptLoading():void {
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"service":"laco","include":"body","coverage":{"document":"any","page":"any"},"icon":"all","exclude":".nolaco, .more-link, .pager"}');
    \Drupal::configFactory()->getEditable('oe_webtools_laco_widget.settings')
      ->set('ignore', ['/fr/', '/en/'])
      ->save();
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"service":"laco","include":"body","coverage":{"document":"any","page":"any"},"icon":"all","exclude":".nolaco, .more-link, .pager","ignore":["\/fr\/","\/en\/"]}');
  }

}
