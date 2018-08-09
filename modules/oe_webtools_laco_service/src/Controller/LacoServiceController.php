<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\oe_webtools_laco_service\LacoServiceHeaders;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller that returns Laco translation information for a given entity.
 */
class LacoServiceController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a LacoServiceController instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $routeMatch;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('language_manager')
    );
  }

  /**
   * The Laco service page callback for entity routes.
   *
   * Returns a response containing information on whether
   * a given entity has a translation for a specific language.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current Request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A Response instance.
   */
  public function getEntityLacoLanguage(Request $request):Response {
    $entity = $this->getEntity();

    // By this point it's guaranteed we have a Laco language requested.
    $language = $request->headers->get(LacoServiceHeaders::HTTP_HEADER_LANGUAGE_NAME);
    $available = FALSE;
    if ($entity->hasTranslation($language)) {
      $available = TRUE;
    }

    $status_header = $available ? '200 OK' : '404 Not found';
    $response = new Response();
    $response->headers->set('Status', $status_header);
    return $response;
  }

  /**
   * The Laco service page callback for all non-entity pages.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current Request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A Response instance.
   */
  public function getDefaultLacoLanguage(Request $request):Response {
    $response = new Response();
    $language = $request->headers->get(LacoServiceHeaders::HTTP_HEADER_LANGUAGE_NAME);
    $available = $this->languageManager->getLanguage($language) !== NULL ? TRUE : FALSE;

    $status_header = $available ? '200 OK' : '404 Not found';
    $response->headers->set('Status', $status_header);
    return $response;
  }

  /**
   * Returns the entity from the route.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   A content entity of any type.
   */
  protected function getEntity():ContentEntityInterface {
    $route = $this->routeMatch->getRouteObject();
    $entity_type = $route->getOption('_oe_laco_entity_type');
    return $this->routeMatch->getParameter($entity_type);
  }

}
