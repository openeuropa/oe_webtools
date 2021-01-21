<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_etrans\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block that displays the Webtools ETrans button.
 *
 * @Block(
 *   id = "oe_webtools_etrans",
 *   admin_label = @Translation("OpenEuropa Webtools ETrans"),
 *   category = @Translation("Webtools")
 * )
 */
class ETransBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Creates an ETransBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $json = [
      'service' => 'etrans',
      'languages' => [
        'exclude' => [$this->languageManager->getCurrentLanguage()->getId()],
      ],
      'renderAs' => [
        'button' => FALSE,
        'icon' => FALSE,
        'link' => TRUE,
      ],
    ];
    $build = [
      '#cache' => [
        'contexts' => ['languages:' . LanguageInterface::TYPE_INTERFACE],
      ],
    ];
    $build['content'] = [
      '#attached' => ['library' => ['oe_webtools/drupal.webtools-smartloader']],
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => Json::encode($json),
      '#attributes' => ['type' => 'application/json'],
    ];

    return $build;
  }

}
