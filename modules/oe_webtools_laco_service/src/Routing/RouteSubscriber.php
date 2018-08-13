<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service\Routing;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Laco service route subscriber.
 *
 * Subscribes to the route generation to alter content entity routes and add
 * a Laco flag.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RouteSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RoutingEvents::ALTER][] = ['onAlterRoutes', -1000];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    // Only include routes for content entities with canonical links.
    $definitions = $this->entityTypeManager->getDefinitions();
    $definitions = array_filter($definitions, function (EntityTypeInterface $definition) {
      return $definition instanceof ContentEntityTypeInterface && $definition->hasLinkTemplate('canonical');
    });

    foreach ($definitions as $definition) {
      $entity_type = $definition->id();
      $route_name = 'entity.' . $entity_type . '.canonical';
      $route = $collection->get($route_name);
      if (!$route instanceof Route) {
        continue;
      }

      $route->setOption('_oe_laco_entity_type', $entity_type);
    }
  }

}
