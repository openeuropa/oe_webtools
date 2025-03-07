<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools\Functional;

use Drupal\Core\Test\FunctionalTestSetupTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;

/**
 * Tests that the configured settings are correctly outputted in the page.
 *
 * @group oe_webtools
 */
class SmartLoaderDependenciesTest extends BrowserTestBase {

  use FunctionalTestSetupTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'path_alias',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test override of the smartload library js file.
   */
  public function testLibraryOverride(): void {
    $this->container->get('module_installer')->install(['oe_webtools_analytics']);
    $this->configureWebtoolsAnalytics();
    $this->container->get('kernel')->invalidateContainer();

    $this->writeSettings([
      'settings' => [
        'webtools_smart_loader_js_url' => (object) [
          'value' => '//webtools.europa.eu/load.js',
          'required' => TRUE,
        ],
      ],
    ]);

    $this->drupalGet('random/path');
    $this->assertSession()->responseContains('<script src="//webtools.europa.eu/load.js');
  }

  /**
   * Tests if Webtools library is present on the page during module install.
   *
   * @param string $module
   *   The module name.
   * @param string $url
   *   The module name.
   * @param string|null $setup_method
   *   The method responsible for enabling required configuration.
   *
   * @dataProvider getProvidedData
   */
  public function testLibraryLoading(string $module, string $url, $setup_method = NULL): void {
    $this->container->get('module_installer')->install([$module]);
    if ($setup_method) {
      $this->{$setup_method}();
    }
    $this->container->get('kernel')->invalidateContainer();

    $this->drupalGet($url);
    $this->assertSession()->responseContains('<script src="https://webtools.europa.eu/load.js');
  }

  /**
   * Setup method oe_webtools_analytics or oe_webtools_analytics_rules modules.
   */
  public function configureWebtoolsAnalytics() {
    $config = \Drupal::configFactory()
      ->getEditable(AnalyticsEventInterface::CONFIG_NAME)
      ->set('siteID', '123')
      ->set('sitePath', 'ec.europa.eu')
      ->set('instance', 'testing');
    $config->save();
  }

  /**
   * Data provider for testLibraryLoading.
   */
  public static function getProvidedData(): array {
    return [
      ['oe_webtools_analytics', 'random/path', 'configureWebtoolsAnalytics'],
      [
        'oe_webtools_analytics_rules',
        'random/path',
        'configureWebtoolsAnalytics',
      ],
      ['oe_webtools_cookie_consent', 'random/path'],
      ['oe_webtools_globan', 'random/path'],
      // @todo Fix oe_webtools_maps module and add test coverage to this test class.
      ['oe_webtools_laco_widget', 'random/path'],
      // We already have functional tests for OE Webtools social share.
      // @see \Drupal\Tests\oe_webtools\Kernel\SocialShareBlockTest
    ];
  }

}
