<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_page_feedback\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Webtools Default Feedback Form Block.
 *
 * @Block(
 *   id = "oe_webtools_page_feedback_form",
 *   admin_label = @Translation("Page Feedback Form"),
 *   category = @Translation("Webtools"),
 * )
 */
class PageFeedbackFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Creates a PageFeedbackFormBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
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
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $feedback_form_json = [
      'service' => 'dff',
      'id' => $this->configFactory->get('oe_webtools_page_feedback.settings')->get('feedback_form_id'),
      'lang' => $this->languageManager->getCurrentLanguage()->getId(),
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
      '#value' => Json::encode($feedback_form_json),
      '#attributes' => ['type' => 'application/json'],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $config = $this->configFactory->get('oe_webtools_page_feedback.settings');
    if (!$config->get('enabled') || $this->routeMatch->getRouteName() !== 'entity.node.canonical') {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
