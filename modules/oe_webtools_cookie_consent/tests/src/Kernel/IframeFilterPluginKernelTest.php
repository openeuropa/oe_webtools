<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_cookie_consent\Kernel;

use Drupal\Core\Language\LanguageInterface;
use Drupal\filter\FilterPluginCollection;
use Drupal\KernelTests\KernelTestBase;
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
    'oe_webtools_cookie_consent',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['system', 'language']);

    ConfigurableLanguage::createFromLangcode('hu')->save();
  }

  /**
   * Tests the iframe with cookie consent kit filter.
   */
  public function testIframeFilter() {
    $manager = $this->container->get('plugin.manager.filter');
    $filters = new FilterPluginCollection($manager, []);
    $cck_filter = $filters->get('filter_iframe_cck');

    $input = '<p><iframe src="https://example.com?q=a+b&p=1" style="width: 400px; height: 200px;"></iframe></p>';

    // The default language is English.
    $expected = '<p><iframe src="//europa.eu/webtools/crs/iframe/?oriurl=https%3A//example.com%3Fq%3Da%2Bb%26p%3D1&amp;lang=en" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $cck_filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());

    // Test with a different language.
    $this->config('system.site')->set('default_langcode', 'hu')->save();
    $expected = '<p><iframe src="//europa.eu/webtools/crs/iframe/?oriurl=https%3A//example.com%3Fq%3Da%2Bb%26p%3D1&amp;lang=hu" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $cck_filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());
  }

}
