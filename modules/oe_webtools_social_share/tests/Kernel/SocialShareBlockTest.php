<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_social_share\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the Social Share widget provided by oe_webtools.
 */
class SocialShareBlockTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'oe_webtools_social_share',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installSchema('system', 'sequences');
    $this->installConfig(['system']);
  }

  /**
   * Test social share block rendering.
   */
  public function testSocialShareBlockRendering(): void {
    // Setup and render social share block.
    $config = [
      'id' => 'social_share',
      'label' => 'OpenEuropa social share',
      'provider' => 'oe_webtools_social_share',
      'label_display' => '0',
    ];
    $plugin = $this->container->get('plugin.manager.block')->createInstance('social_share', $config);
    $render = $plugin->build();
    // Make sure the block has the required loaders.
    $this->assertEquals(['oe_webtools/drupal.webtools-smartloader'], $render['#attached']['library']);
    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);
    // Make sure that social share block is present.
    $actual = $crawler->filter('script');
    $this->assertEquals('{"service":"share","version":"2.0","networks":["twitter","facebook","linkedin","email","more"],"more":[],"display":"button","stats":true,"selection":true}', $actual->text());
    // Make sure "Share this page" heading is present.
    $this->assertStringContainsString('Share this page', $html);

    $social_share_settings = $this->config('oe_webtools_social_share.settings');
    $social_share_settings->set('icons', TRUE)->save();
    $render = $plugin->build();
    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);
    // Make sure that social share block is present.
    $actual = $crawler->filter('script');
    $this->assertEquals('{"service":"share","version":"2.0","networks":["twitter","facebook","linkedin","email","more"],"more":[],"display":"icons","stats":true,"selection":true}', $actual->text());

    $social_share_settings->set('custom_networks', TRUE)->save();
    $social_share_settings->set('networks', [
      'linkedin' => [
        'visible' => TRUE,
        'weight' => 0,
      ],
      'email' => [
        'visible' => FALSE,
        'weight' => 1,
      ],
      'facebook' => [
        'visible' => TRUE,
        'weight' => 2,
      ],
      'yammer' => [
        'visible' => FALSE,
        'weight' => 3,
      ],
    ])->save();
    $render = $plugin->build();
    $html = (string) $this->container->get('renderer')->renderRoot($render);
    $crawler = new Crawler($html);
    // Make sure that custom networks for social share block is present.
    $actual = $crawler->filter('script');
    $this->assertEquals('{"service":"share","version":"2.0","networks":["linkedin","facebook","more"],"more":["email","yammer"],"display":"icons","stats":true,"selection":true}', $actual->text());
  }

}
