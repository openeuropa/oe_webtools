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
  protected static $modules = [
    'config',
    'system',
    'oe_webtools_globan',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests if the configuration for the Webtools library is present on the page.
   */
  public function testLibraryLoading(): void {
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="https://europa.eu/webtools/load.js?globan=1110" defer></script>');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'light')
      ->set('display_eu_institutions_links', FALSE)
      ->set('override_page_lang', '')
      ->set('sticky', FALSE);
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="https://europa.eu/webtools/load.js?globan=0000" defer></script>');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'dark')
      ->set('display_eu_institutions_links', TRUE)
      ->set('override_page_lang', 'it')
      ->set('sticky', TRUE);
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="https://europa.eu/webtools/load.js?globan=0111&amp;lang=it" defer></script>');
  }

}
