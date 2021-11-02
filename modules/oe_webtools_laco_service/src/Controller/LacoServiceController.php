<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch, LanguageManagerInterface $languageManager, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $routeMatch;
    $this->languageManager = $languageManager;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('language_manager'),
      $container->get('config.factory')
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
  public function getEntityLacoLanguage(Request $request): Response {
    $entity = $this->getEntity();

    // By this point it's guaranteed we have a Laco language requested.
    $language = $request->headers->get(LacoServiceHeaders::HTTP_HEADER_LANGUAGE_NAME);
    return $this->responseFromAvailability($entity->hasTranslation($this->mapLacoLanguageToDrupalLanguage($language)));
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
  public function getDefaultLacoLanguage(Request $request): Response {
    $language = $request->headers->get(LacoServiceHeaders::HTTP_HEADER_LANGUAGE_NAME);

    return $this->responseFromAvailability($this->languageManager->getLanguage($this->mapLacoLanguageToDrupalLanguage($language)) !== NULL);
  }

  /**
   * Returns the entity from the route.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   A content entity of any type.
   */
  protected function getEntity(): ContentEntityInterface {
    $route = $this->routeMatch->getRouteObject();
    $entity_type = $route->getOption('_oe_laco_entity_type');
    return $this->routeMatch->getParameter($entity_type);
  }

  /**
   * Returns a Response object based on the availability of a translation.
   *
   * @param bool $available
   *   Whether a translation is available.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response object.
   */
  protected function responseFromAvailability(bool $available): Response {
    $status = $available ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;
    $response = new Response();
    $response->setStatusCode($status);
    return $response;
  }

  /**
   * Given a langcode from LACO, map it to what the Drupal langcode is.
   *
   * @param string $language
   *   The LACO language code.
   *
   * @return string
   *   The Drupal language code.
   */
  protected function mapLacoLanguageToDrupalLanguage(string $language): string {
    $map = $this->configFactory->get('language.mappings')->get('map') ?? [];
    if (!isset($map[$language])) {
      return $language;
    }

    return $map[$language];
  }

}
