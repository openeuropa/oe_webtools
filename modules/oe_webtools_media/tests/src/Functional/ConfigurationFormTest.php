<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_media\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the config form for the generic widget.
 */
class ConfigurationFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'oe_webtools',
    'oe_webtools_media',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * Tests the config form for the generic widget.
   */
  public function testWebtoolsGenericWidgetConfigForm(): void {
    $this->drupalGet('/admin/config/regional/oe-webtools-media/generic-widget');
    $this->assertSession()->statusCodeEquals(403);
    $user = $this->createUser([
      'administer webtools media configuration',
      'access administration pages',
      'view the administration theme',
    ]);

    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/regional/oe-webtools-media/generic-widget');
    $this->assertSession()->statusCodeEquals(200);

    // Test also the menu link.
    $this->drupalGet('/admin/config');
    $this->clickLink('Webtools Media generic widget');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Webtools Media generic widget settings');
    $this->assertSession()->fieldExists('Blacklist');
    $list = [
      'charts',
      'chart',
      'racing',
      'map',
      'smk',
      'opwidget',
      'etrans',
    ];
    $this->assertSession()->fieldValueEquals('Blacklist', implode(PHP_EOL, $list));

    // Add another blacklist item.
    $list[] = 'extra';
    $this->getSession()->getPage()->fillField('Blacklist', implode("\r\n", $list));
    $this->getSession()->getPage()->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->fieldValueEquals('Blacklist', implode("\n", $list));
    $this->assertEquals($list, \Drupal::configFactory()->get('oe_webtools_media.generic_widget_settings')->get('blacklist'));
  }

}
