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
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"globan","theme":"dark","logo":true,"link":true,"mode":false}</script>');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'light')
      ->set('display_eu_institutions_links', FALSE)
      ->set('override_page_lang', '')
      ->set('sticky', FALSE);
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"globan","theme":"light","logo":false,"link":false,"mode":false}</script>');

    $config = \Drupal::configFactory()
      ->getEditable('oe_webtools_globan.settings')
      ->set('display_eu_flag', FALSE)
      ->set('background_theme', 'dark')
      ->set('display_eu_institutions_links', TRUE)
      ->set('override_page_lang', 'it')
      ->set('sticky', TRUE);
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"globan","theme":"dark","logo":false,"link":true,"mode":true,"lang":"it"}</script>');

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
    $this->assertSession()->responseContains('<script type="application/json">{"utility":"globan","theme":"dark","logo":false,"link":true,"mode":true,"lang":"it","zindex":0}</script>');
  }

}
