<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Kernel;

use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the Social Share widget provided by oe_webtools.
 */
class SocialShareBlockTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'user',
    'oe_webtools_social_share',

  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installConfig(['system']);
  }

  /**
   * Test social share block rendering.
   */
  public function testSearchBlockRendering(): void {
    // Setup and render search form block.
    $config = [
      'id' => 'social_share',
      'label' => 'OpenEuropa social share',
      'provider' => 'oe_webtools_social_share',
      'label_display' => '0',
    ];
    $render = $this->buildBlock('social_share', $config);
    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);
    // Make sure that search form block is present.
    $actual = $crawler->filter('script');
    $this->assertEquals('{"service":"share","popup":false,"selection":true,"to":["more","twitter","facebook","linkedin","e-mail"],"stats":true}', $actual->text());
  }

  /**
   * Builds and returns the renderable array for a block.
   *
   * @param string $block_id
   *   The ID of the block.
   * @param array $config
   *   An array of configuration.
   *
   * @return array
   *   A renderable array representing the content of the block.
   */
  protected function buildBlock(string $block_id, array $config): array {
    /** @var \Drupal\Core\Block\BlockBase $plugin */
    $plugin = $this->container->get('plugin.manager.block')->createInstance($block_id, $config);
    // Inject runtime contexts.
    if ($plugin instanceof ContextAwarePluginInterface) {
      $contexts = $this->container->get('context.repository')->getRuntimeContexts($plugin->getContextMapping());
      $this->container->get('context.handler')->applyContextMapping($plugin, $contexts);
    }
    return $plugin->build();
  }

}
