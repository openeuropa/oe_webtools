<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_globan\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_webtools\Traits\ApplicationJsonAssertTrait;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests that the configured settings are correctly output in the page.
 *
 * @group oe_webtools_globan
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
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"globan","theme":"dark","logo":true,"link":true,"mode":false}');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'light')
      ->set('display_eu_institutions_links', FALSE)
      ->set('override_page_lang', '')
      ->set('sticky', FALSE);
    $config->save();

    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"globan","theme":"light","logo":false,"link":false,"mode":false}');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'dark')
      ->set('display_eu_institutions_links', TRUE)
      ->set('override_page_lang', 'it')
      ->set('sticky', TRUE);
    $config->save();

    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"globan","theme":"dark","logo":false,"link":true,"mode":true,"lang":"it"}');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'dark')
      ->set('display_eu_institutions_links', TRUE)
      ->set('override_page_lang', 'it')
      ->set('sticky', TRUE)
      ->set('zindex', 0);

    $config->save();

    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"globan","theme":"dark","logo":false,"link":true,"mode":true,"lang":"it","zindex":0}');
  }

  /**
   * Tests that the settings form changes are reflected on the page.
   */
  public function testGlobanSettingsForm(): void {
    $user = $this->createUser([
      'administer webtools globan',
      'access administration pages',
    ]);

    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/oe_webtools_globan');
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Display the EU flag', 'Yes');
    $page->selectFieldOption('Background theme', 'Dark');
    $page->selectFieldOption('Link to all EU Institutions and bodies', 'Yes');
    $page->selectFieldOption('Sticky', 'Yes');
    $page->pressButton('Save configuration');
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"globan","theme":"dark","logo":true,"link":true,"mode":true}');

    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/oe_webtools_globan');
    $page->selectFieldOption('Display the EU flag', 'No');
    $page->selectFieldOption('Background theme', 'Light');
    $page->selectFieldOption('Link to all EU Institutions and bodies', 'No');
    $page->selectFieldOption('Sticky', 'No');
    $page->pressButton('Save configuration');
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"globan","theme":"light","logo":false,"link":false,"mode":false}');

    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/oe_webtools_globan');
    $page->selectFieldOption('Override page language', 'English');
    $page->fillField('Z-index', '41');
    $page->pressButton('Save configuration');
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"globan","theme":"light","logo":false,"link":false,"mode":false,"lang":"en","zindex":41}');
  }

}
