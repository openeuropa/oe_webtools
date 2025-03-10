<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_cookie_consent\Kernel;

use Drupal\Core\Language\LanguageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\filter\FilterPluginCollection;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the iframe filter plugin.
 */
class IframeFilterPluginKernelTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'system',
    'filter',
    'language',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system', 'language']);

    ConfigurableLanguage::createFromLangcode('hu')->save();
  }

  /**
   * Tests the iframe with cookie consent kit filter.
   */
  public function testIframeFilter() {
    // Enable the module inside test method to allow other tests to initialize
    // with specific settings.
    $this->enableModules(['oe_webtools_cookie_consent']);
    $manager = $this->container->get('plugin.manager.filter');
    $filters = new FilterPluginCollection($manager, []);
    $cck_filter = $filters->get('filter_iframe_cck');

    $input = '<p><iframe src="https://example.com?q=a+b&p=1" style="width: 400px; height: 200px;"></iframe></p>';

    // The default language is English.
    $expected = '<p><iframe src="https://webtools.europa.eu/crs/iframe/?oriurl=https%3A//example.com%3Fq%3Da%2Bb%26p%3D1&amp;lang=en" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $cck_filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());

    // Test with a different language.
    $this->config('system.site')->set('default_langcode', 'hu')->save();
    $expected = '<p><iframe src="https://webtools.europa.eu/crs/iframe/?oriurl=https%3A//example.com%3Fq%3Da%2Bb%26p%3D1&amp;lang=hu" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $cck_filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());
  }

  /**
   * Tests the iframe URL override through settings.
   */
  public function testOverrideCookieConsentExternalUrls(): void {
    // Initialize settings for overriding default iframe URL.
    $this->setSetting('webtools_cookie_consent_embed_cookie_url', 'https://webtools.europa.eu/crs/iframe/');
    $this->enableModules(['oe_webtools_cookie_consent']);
    $manager = \Drupal::service('plugin.manager.filter');
    $filters = new FilterPluginCollection($manager, []);
    $cck_filter = $filters->get('filter_iframe_cck');
    $input = '<p><iframe src="https://example.com?q=a+b&p=1" style="width: 400px; height: 200px;"></iframe></p>';

    // Assert application of iframe URL through settings.
    $expected = '<p><iframe src="https://webtools.europa.eu/crs/iframe/?oriurl=https%3A//example.com%3Fq%3Da%2Bb%26p%3D1&amp;lang=en" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $cck_filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());
  }

}
