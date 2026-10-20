<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_laco_widget\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_webtools\Traits\ApplicationJsonAssertTrait;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Check if the Laco widget code is on the front page.
 *
 * @group batch1
 */
class LacoWidgetTest extends BrowserTestBase {

  use ApplicationJsonAssertTrait;
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
    \Drupal::configFactory()->getEditable('oe_webtools_laco_widget.settings')
      ->set('coverage.document', 'false')
      ->set('coverage.page', 'false')
      ->save();
    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"laco"');
  }

  /**
   * Tests the Laco widget settings form.
   */
  public function testLacoSettingsForm(): void {
    $this->drupalLogin($this->createUser(['administer webtools laco widget configuration']));
    $this->drupalGet('admin/config/regional/oe_webtools_laco_widget');
    $assert_session = $this->assertSession();
    $assert_session->pageTextContains('Webtools Laco Widget settings');
    $assert_session->pageTextNotContains('header-test');
    $assert_session->pageTextNotContains('footer-test');

    // The include field is required.
    $page = $this->getSession()->getPage();
    $page->fillField('Include', '');
    $page->pressButton('Save configuration');
    $assert_session->pageTextContains('Include field is required.');

    $page->fillField('Include', 'header-test');
    $page->fillField('Exclude', 'footer-test');
    $page->fillField('Ignore', '/fr/');
    $page->selectFieldOption('Document', 'other');
    $page->selectFieldOption('Page', 'other');
    $page->selectFieldOption('Icon', 'dot');
    $page->pressButton('Save configuration');
    $assert_session->pageTextContains('The configuration options have been saved.');
    $assert_session->fieldValueEquals('Include', 'header-test');
    $assert_session->fieldValueEquals('Exclude', 'footer-test');
    $assert_session->fieldValueEquals('Ignore', '/fr/');
    $assert_session->fieldValueEquals('Document', 'other');
    $assert_session->fieldValueEquals('Page', 'other');
    $assert_session->fieldValueEquals('Icon', 'dot');

    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"service":"laco","include":"header-test","coverage":{"document":"other","page":"other"},"icon":"dot","exclude":"footer-test","ignore":["\/fr\/"]}');
  }

}
