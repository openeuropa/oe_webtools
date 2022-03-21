<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the configured settings are correctly output in the page.
 *
 * @group oe_webtools_analytics
 */
class ConfigurationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_webtools_analytics',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests if the configuration for the Webtools library is present on the page.
   */
  public function testLibraryLoading(): void {
    $user = $this->createUser(['administer webtools analytics']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/oe_webtools_analytics');
    $page = $this->getSession()->getPage();
    $page->fillField('Site ID', '123');
    $page->fillField('Site path', 'ec.europa.eu');
    $page->fillField('Instance', 'testing');
    $page->pressButton('Save configuration');

    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"instance":"testing"}</script>');

    $this->drupalGet('not-existing-page');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"is404":true,"instance":"testing"}</script>');

    $this->drupalGet('admin');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"is403":true,"instance":"testing"}</script>');

    // Test the cache invalidation.
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/oe_webtools_analytics');
    $this->getSession()->getPage()->fillField('Site ID', '123e4567-e89b-12d3-a456-426614174000');
    $this->getSession()->getPage()->pressButton('Save configuration');

    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123e4567-e89b-12d3-a456-426614174000","sitePath":["ec.europa.eu"],"instance":"testing"}</script>');
  }

}
