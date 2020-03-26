<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_globan;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\AdminContext;

/**
 * Default implementation for the 'oe_webtools_goban.visibility' service.
 */
class GlobanVisibility implements GlobanVisibilityInterface {

  use ConditionAccessResolverTrait;

  /**
   * The route admin context service.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $routeAdminContext;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The condition plugin manager service.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $conditionManager;

  /**
   * Constructs a new service instance.
   *
   * @param \Drupal\Core\Routing\AdminContext $route_admin_context
   *   The route admin context service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $condition_manager
   *   The condition plugin manager service.
   */
  public function __construct(AdminContext $route_admin_context, ConfigFactoryInterface $config_factory, PluginManagerInterface $condition_manager) {
    $this->routeAdminContext = $route_admin_context;
    $this->configFactory = $config_factory;
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldDisplayBanner(): bool {
    if ($this->routeAdminContext->isAdminRoute()) {
      return FALSE;
    }

    $settings = $this->configFactory->get('oe_webtools_globan.settings');
    // If no page pattern is set, return TRUE.
    if (!$pages = trim($settings->get('visibility.pages'))) {
      return TRUE;
    }

    $negate = ['show' => FALSE, 'hide' => TRUE][$settings->get('visibility.action')];

    /** @var \Drupal\Core\Condition\ConditionInterface $condition */
    $condition = $this->conditionManager->createInstance('request_path', [
      'negate' => $negate,
      'pages' => $pages,
    ]);

    return $this->resolveConditions([$condition], 'and');
  }

}
