<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_laco_widget\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests the Laco widget settings form behaviour.
 *
 * @group oe_webtools_laco_widget
 * @group batch1
 */
class LacoWidgetSettingsFormTest extends WebDriverTestBase {

  use CachedDatabaseInstallTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'language',
    'oe_webtools_analytics',
    'oe_webtools_analytics_rules',
    'oe_webtools_globan',
    'oe_webtools_hotjar',
    'oe_webtools_js_mock',
    'oe_webtools_laco_widget',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cacheDbInstall = TRUE;
    parent::setUp();
  }

  /**
   * Tests that the widget settings are only visible when the widget is enabled.
   */
  public function testLacoSettingsVisibility(): void {
    $this->drupalLogin($this->createUser(['administer webtools laco widget configuration']));
    $this->drupalGet('admin/config/regional/oe_webtools_laco_widget');
    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    $assert_session->checkboxChecked('Enable LACO widget');
    $intro = $assert_session->elementExists('xpath', '//*[contains(text(), "For more information check the module")]');
    $this->assertTrue($intro->isVisible());
    $this->assertTrue($assert_session->fieldExists('Exclude')->isVisible());

    $page->uncheckField('Enable LACO widget');
    $this->assertFalse($intro->isVisible());
    $this->assertFalse($assert_session->fieldExists('Exclude')->isVisible());

    $page->checkField('Enable LACO widget');
    $this->assertTrue($intro->isVisible());
    $this->assertTrue($assert_session->fieldExists('Exclude')->isVisible());
  }

}
