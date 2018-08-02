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
 * Subscribes to the route generation to add our custom route "duplicates" to
 * certain entity type canonical routes.
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
  public static function getSubscribedEvents() {
    // We need this to run at the very last moment because we want to inherit
    // route definitions which may have already been altered by other modules.
    $events[RoutingEvents::ALTER][] = ['onAlterRoutes', -1000];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $definitions = $this->entityTypeManager->getDefinitions();

    // Eliminate config entity types and those for which we won't be checking
    // Laco translations.
    $eliminate = [
      'block_content',
      'comment',
      'contact_message',
      'shortcut',
      'user',
      'file',
      'menu_link_content',
    ];

    $definitions = array_filter($definitions, function (EntityTypeInterface $definition) use ($eliminate) {
      return $definition instanceof ContentEntityTypeInterface && !in_array($definition->id(), $eliminate);
    });

    foreach ($definitions as $definition) {
      $entity_type = $definition->id();
      $route_name = 'entity.' . $entity_type . '.canonical';
      $route = $collection->get($route_name);
      if (!$route instanceof Route) {
        continue;
      }

      $new_route = clone $route;
      $new_route->setMethods(['GET', 'HEAD']);
      $new_route->setRequirement('_format', 'laco');
      // We specify the entity type so that in the controller we can easily
      // load the parameter from the route match.
      // @see LacoServiceController::getEntity().
      $new_route->setOption('_oe_laco_entity_type', $entity_type);
      $new_route->setDefault('_controller', '\Drupal\oe_webtools_laco_service\Controller\LacoServiceController::getLacoLanguage');
      $collection->add('oe_webtools_laco_service.' . $route_name, $new_route);
    }
  }

}
