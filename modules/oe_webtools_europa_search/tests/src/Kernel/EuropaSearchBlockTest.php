<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_europa_search\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the Europa Search widget provided by oe_webtools.
 */
class EuropaSearchBlockTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'language',
    'locale',
    'oe_webtools_europa_search',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installConfig(['system']);
    ConfigurableLanguage::createFromLangcode('fr')->save();
  }

  /**
   * Test europa search block rendering.
   */
  public function testSocialShareBlockRendering(): void {
    // Setup and render europa search block.
    $config = [
      'id' => 'europa_search',
      'label' => 'OpenEuropa social share',
      'provider' => 'oe_webtools_europa_search',
      'label_display' => '0',
    ];
    foreach (['fr', 'en'] as $langcode) {
      \Drupal::configFactory()->getEditable('system.site')->set('default_langcode', $langcode)->save();
      \Drupal::service('kernel')->rebuildContainer();
      $plugin = $this->container->get('plugin.manager.block')
        ->createInstance('europa_search', $config);
      $render = $plugin->build();
      // Make sure the block has the required loaders.
      $this->assertEquals(['oe_webtools/drupal.webtools-smartloader'], $render['content']['#attached']['library']);
      $html = (string) $this->container->get('renderer')->renderRoot($render);
      $crawler = new Crawler($html);
      // Make sure that the europa search json is present.
      $actual = $crawler->filter('script');
      $this->assertEquals('{"service":"search","lang":"' . $langcode . '","results":"out"}', $actual->text());
    }
  }

}
