<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service\EventSubscriber;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\oe_webtools_laco_service\LacoServiceHeaders;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Subscribes to the request Kernel event.
 */
class LacoServiceDefaultSubscriber implements EventSubscriberInterface {

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
   * Constructs a new LacoServiceDefaultSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager) {
    $this->routeMatch = $routeMatch;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.request'] = ['onRequest'];
    return $events;
  }

  /**
   * Responds to the request event.
   *
   * If it's a Laco request, respond directly with information about the
   * existence of the requested language on the site.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The subscriber event.
   */
  public function onRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    if (!$request->attributes->get('_format') == 'laco') {
      return;
    }

    $route = $this->routeMatch->getRouteObject();
    // We don't do anything if we are looking at an entity route because that is
    // handled by a dedicated controller.
    if ($route->hasOption('_oe_laco_entity_type')) {
      return;
    }

    $response = new Response();
    $language = $request->headers->get(LacoServiceHeaders::HTTP_HEADER_LANGUAGE_NAME);
    $available = $this->languageManager->getLanguage($language) !== NULL ? TRUE : FALSE;

    $status_header = $available ? '200 OK' : '404 Not found';
    $response->headers->set('Status', $status_header);
    $event->setResponse($response);
    $event->stopPropagation();
  }

}
