<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_analytics\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_webtools\Traits\ApplicationJsonAssertTrait;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests that the configured settings are correctly output in the page.
 *
 * @group oe_webtools_analytics
 * @group batch1
 */
class ConfigurationTest extends BrowserTestBase {

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
   * Tests if the configuration for the Webtools library is present on the page.
   */
  public function testLibraryLoading(): void {
    $user = $this->createUser(['administer webtools analytics']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/oe_webtools_analytics');
    $this->assertSession()->pageTextContains('Webtools Analytics settings');
    $page = $this->getSession()->getPage();
    $page->fillField('Site ID', '123');
    $page->fillField('Site path', 'ec.europa.eu');
    $page->fillField('Instance', 'testing');
    $page->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->fieldValueEquals('Site ID', '123');
    $this->assertSession()->fieldValueEquals('Site path', 'ec.europa.eu');
    $this->assertSession()->fieldValueEquals('Instance', 'testing');

    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"analytics","siteID":"123","sitePath":["ec.europa.eu"],"instance":"testing"}');

    $this->drupalGet('not-existing-page');
    $this->assertBodyContainsApplicationJson('{"utility":"analytics","siteID":"123","sitePath":["ec.europa.eu"],"is404":true,"instance":"testing"}');

    $this->drupalGet('admin');
    $this->assertBodyContainsApplicationJson('{"utility":"analytics","siteID":"123","sitePath":["ec.europa.eu"],"is403":true,"instance":"testing"}');

    // Test the cache invalidation.
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/oe_webtools_analytics');
    $this->getSession()->getPage()->fillField('Site ID', '123e4567-e89b-12d3-a456-426614174000');
    $this->getSession()->getPage()->pressButton('Save configuration');

    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"analytics","siteID":"123e4567-e89b-12d3-a456-426614174000","sitePath":["ec.europa.eu"],"instance":"testing"}');
  }

}
