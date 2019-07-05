<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_cookie_consent\Functional;

use Drupal\oe_webtools_cookie_consent\CookieConsentEventInterface;
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
    'oe_webtools',
    'oe_webtools_cookie_consent',
  ];

  /**
   * Tests if the configuration for the Webtools library is present on the page.
   */
  public function testLibraryLoading(): void {
    $config = \Drupal::configFactory()
      ->getEditable(CookieConsentEventInterface::CONFIG_NAME)
      ->set('cckEnabled', FALSE);
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('<script src="//europa.eu/wel/cookie-consent/consent.js"></script>');

    // Test the cache invalidation.
    $config = \Drupal::configFactory()
      ->getEditable(CookieConsentEventInterface::CONFIG_NAME)
      ->set('cckEnabled', TRUE);
    $config->save();

    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('<script src="//europa.eu/wel/cookie-consent/consent.js"></script>');
  }

}
