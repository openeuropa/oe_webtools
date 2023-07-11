<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_captcha\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the configured settings are correctly set.
 *
 * @group oe_webtools_captcha
 */
class ConfigurationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'oe_webtools_captcha',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests The configuration is well saved from the form to the config.
   */
  public function testConfigurationForm(): void {
    // Assert the default configuration.
    $captcha_settings = \Drupal::config('oe_webtools_captcha.settings');
    $validation_endpoint = $captcha_settings->get('validationEndpoint');
    $this->assertEquals('https://europa.eu/webtools/rest/captcha/verify', $validation_endpoint);

    // Change the config  via the user.
    $user = $this->createUser(['administer webtools captcha']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/config/system/oe_webtools_captcha');
    $page = $this->getSession()->getPage();
    $page->fillField('Validation endpoint', 'http://example.com');
    $page->pressButton('Save configuration');

    // Get the config again.
    $captcha_settings = \Drupal::config('oe_webtools_captcha.settings');
    $validation_endpoint = $captcha_settings->get('validationEndpoint');
    $this->assertEquals('http://example.com', $validation_endpoint);
  }

}
