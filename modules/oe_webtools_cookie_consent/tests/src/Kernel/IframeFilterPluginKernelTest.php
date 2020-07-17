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
  public static $modules = [
    'system',
    'filter',
    'language',
    'oe_webtools_cookie_consent',
  ];

  /**
   * Filter.
   *
   * @var \Drupal\filter\Plugin\FilterInterface[]
   */
  protected $filter;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['system', 'language']);

    ConfigurableLanguage::createFromLangcode('hu')->save();

    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filter = $bag->get('filter_iframe_cck');
  }

  /**
   * Tests the iframe with cookie consent kit filter.
   */
  public function testIframeFilter() {
    $input = '<p><iframe src="https://example.com" style="width: 400px; height: 200px;"></iframe></p>';

    // The default language is English.
    $expected = '<p><iframe src="//europa.eu/webtools/crs/iframe/?oriurl=https://example.com&amp;lang=en" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $this->filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());

    // Test with a different language.
    $this->config('system.site')->set('default_langcode', 'hu')->save();
    $expected = '<p><iframe src="//europa.eu/webtools/crs/iframe/?oriurl=https://example.com&amp;lang=hu" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $this->filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());
  }

}
