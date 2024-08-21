<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_social_share\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Social Share' Block.
 *
 * @Block(
 *   id = "social_share",
 *   admin_label = @Translation("Social Share"),
 *   category = @Translation("Webtools"),
 * )
 */
class SocialShareBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates a SocialShareBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config */
    $config = $this->configFactory->get('oe_webtools_social_share.settings');
    $social_share_json = [
      'service' => 'share',
      'version' => '2.0',
      'networks' => [
        'twitter',
        'facebook',
        'linkedin',
        'email',
        'more',
      ],
      'display' => $config->get('icons') ? 'icons' : 'button',
      'stats' => TRUE,
      'selection' => TRUE,
    ];
    return [
      '#theme' => 'oe_webtools_social_share',
      '#title' => $this->t('Share this page'),
      '#icons_json' => Markup::create(Json::encode($social_share_json)),
      '#attached' => ['library' => ['oe_webtools/drupal.webtools-smartloader']],
    ];
  }

}
