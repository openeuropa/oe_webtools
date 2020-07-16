<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_cookie_consent\Kernel;

use Drupal\Core\Language\LanguageInterface;
use Drupal\filter\FilterPluginCollection;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests filter plugins.
 */
class FilterPluginKernelTest extends KernelTestBase {

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
   * Filters.
   *
   * @var \Drupal\filter\Plugin\FilterInterface[]
   */
  protected $filters;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['system', 'language']);

    ConfigurableLanguage::createFromLangcode('hu')->save();

    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filters = $bag->getAll();
  }

  /**
   * Tests the iframe with cookie consent kit filter.
   */
  public function testIframeFilter() {
    $filter = $this->filters['filter_iframe_cck'];
    $input = '<p><iframe src="hello.html" style="width: 400px; height: 200px;"></iframe></p>';

    // The default language is English.
    $expected = '<p><iframe src="//europa.eu/webtools/crs/iframe/?oriurl=hello.html&amp;lang=en" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());

    // Test with a different language.
    $this->config('system.site')->set('default_langcode', 'hu')->save();
    $expected = '<p><iframe src="//europa.eu/webtools/crs/iframe/?oriurl=hello.html&amp;lang=hu" style="width: 400px; height: 200px;"></iframe></p>';
    $this->assertEquals($expected, $filter->process($input, LanguageInterface::LANGCODE_NOT_SPECIFIED)->getProcessedText());
  }

}
