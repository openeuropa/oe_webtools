<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_globan\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the configured settings are correctly output in the page.
 *
 * @group oe_webtools_globan
 */
class ConfigurationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_webtools',
    'oe_webtools_globan',
  ];

  /**
   * Tests if the configuration for the Webtools library is present on the page.
   */
  public function testLibraryLoading(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="//europa.eu/webtools/load.js?globan=111" defer></script>');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', '0')
      ->set('background_theme', '0')
      ->set('eu_institutions_links', '2')
      ->set('override_page_lang', '');
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="//europa.eu/webtools/load.js?globan=002" defer></script>');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', '0')
      ->set('background_theme', '1')
      ->set('eu_institutions_links', '1')
      ->set('override_page_lang', 'it');
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="//europa.eu/webtools/load.js?globan=011&amp;lang=it" defer></script>');
  }

}
