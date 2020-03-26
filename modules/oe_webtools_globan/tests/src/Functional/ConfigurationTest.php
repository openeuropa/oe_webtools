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
    'contact',
    'oe_webtools_globan',
  ];

  /**
   * Tests if the configuration for the Webtools library is present on the page.
   */
  public function testLibraryLoading(): void {
    $config = $this->config('oe_webtools_globan.settings');

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="//europa.eu/webtools/load.js?globan=111" defer></script>');

    $config
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'light')
      ->set('display_eu_institutions_links', FALSE)
      ->set('override_page_lang', '')
      ->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="//europa.eu/webtools/load.js?globan=000" defer></script>');

    $config
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'dark')
      ->set('display_eu_institutions_links', TRUE)
      ->set('override_page_lang', 'it')
      ->save();

    $this->drupalGet('/user');
    $this->assertSession()->responseContains('<script src="//europa.eu/webtools/load.js?globan=011&amp;lang=it" defer></script>');

    // Show only on front page.
    $config->set('visibility', ['action' => 'show', 'pages' => '<front>'])->save();
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="//europa.eu/webtools/load.js?globan=011&amp;lang=it" defer></script>');
    $this->drupalGet('/contact');
    $this->assertSession()->responseNotContains('?globan=');
  }

}
