<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service\EventSubscriber;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribes to the request Kernel event.
 */
class LacoServiceSubscriber implements EventSubscriberInterface {

  /**
   * The current route match.
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
   * The controller resolver.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  protected $controllerResolver;

  /**
   * Constructs a new LacoServiceDefaultSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controllerResolver
   *   The controller resolver.
   */
  public function __construct(RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager, ControllerResolverInterface $controllerResolver) {
    $this->routeMatch = $routeMatch;
    $this->languageManager = $languageManager;
    $this->controllerResolver = $controllerResolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::CONTROLLER] = ['onController'];
    return $events;
  }

  /**
   * Responds to the controller event.
   *
   * Changes the controller on Laco requests.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
   *   The dispatched event.
   */
  public function onController(FilterControllerEvent $event): void {
    if (!$event->getRequest()->attributes->get('_is_laco_request')) {
      return;
    }

    $request = $event->getRequest();
    if (!$request->attributes->get('_access_result') instanceof AccessResultAllowed) {
      return;
    }

    $method = $this->routeMatch->getRouteObject()->hasOption('_oe_laco_entity_type') ? 'getEntityLacoLanguage' : 'getDefaultLacoLanguage';
    $controller = $this->controllerResolver->getControllerFromDefinition("Drupal\oe_webtools_laco_service\Controller\LacoServiceController::$method");
    $event->setController($controller);
  }

}
