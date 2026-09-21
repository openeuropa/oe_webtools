<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_etrans_lc2023\EventSubscriber;

use Drupal\block\BlockRepositoryInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Url;
use Drupal\oe_webtools_etrans_lc2023\Service\ETransService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribe to KernelEvents::REQUEST events.
 */
class ETransLanguagesEventSubscriber implements EventSubscriberInterface {

  /**
   * Aggregated asset routes that should not be redirected.
   */
  const ASSET_ROUTES = ['system.js_asset', 'system.css_asset'];

  /**
   * The schemes of all available StreamWrapper.
   *
   * @var array
   */
  protected $schemes;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Contains entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * E-translation service.
   *
   * @var \Drupal\oe_webtools_etrans_lc2023\Service\ETransService
   */
  protected $etransService;

  /**
   * Block repository.
   *
   * @var \Drupal\Core\Block\BlockRepositoryInterface
   */
  protected $blockRepository;

  /**
   * DisabledLanguagesEventSubscriber constructor.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $streamWrapperManager
   *   The stream wrapper manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Manages entity type plugin definitions.
   * @param \Drupal\oe_webtools_etrans_lc2023\Service\ETransService $etransService
   *   eTranslation service.
   * @param \Drupal\block\BlockRepositoryInterface $blockRepository
   *   The block repository.
   */
  public function __construct(StreamWrapperManager $streamWrapperManager, LanguageManagerInterface $languageManager, EntityTypeManagerInterface $entityTypeManager, ETransService $etransService, BlockRepositoryInterface $blockRepository) {
    $this->schemes = array_keys($streamWrapperManager->getWrappers());
    $this->languageManager = $languageManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->etransService = $etransService;
    $this->blockRepository = $blockRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // On a normal request.
    $events[KernelEvents::REQUEST][] = ['checkForEuLanguageAndRedirect'];
    // On access denied request.
    $events[KernelEvents::EXCEPTION][] = [
      'checkForEuLanguageAndRedirect',
      0,
    ];
    return $events;
  }

  /**
   * Check if the current request should be redirected.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function checkForEuLanguageAndRedirect(RequestEvent $event): void {
    if (!$this->isBlockOnPage()) {
      return;
    }
    // Do not redirect if this is a file.
    if ($this->isFileRequest($event)) {
      return;
    }
    // Do not redirect aggregated CSS/JS files.
    if ($this->isAssetRoute($event)) {
      return;
    }
    // Do not redirect admin route.
    if ($this->isAdminRoute($event)) {
      return;
    }
    // Do nothing for default language.
    if ($this->isDefaultLanguage()) {
      return;
    }
    // Check if current language is European and it should be handled
    // through e-translation component.
    $european_lang = $this->etransService->isLanguageEuropean();
    if (!$european_lang instanceof LanguageInterface) {
      return;
    }

    // Get redirect URL.
    $url = $this->getRedirectUrl($event, $european_lang);

    // Set the response.
    $this->setResponse($event, $url, $european_lang);
  }

  /**
   * Check if it's file request.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Event object.
   *
   * @return bool
   *   Returns true or false.
   */
  private function isFileRequest(RequestEvent $event): bool {
    $params = $event->getRequest()->attributes->all();
    return isset($params['scheme']) && in_array($params['scheme'], $this->schemes);
  }

  /**
   * Check if it's asset route.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Event object.
   *
   * @return bool
   *   Returns true or false.
   */
  private function isAssetRoute(RequestEvent $event): bool {
    $route_name = RouteMatch::createFromRequest($event->getRequest())->getRouteName();
    return in_array($route_name, self::ASSET_ROUTES);
  }

  /**
   * Check if it's admin route.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Event object.
   *
   * @return bool
   *   Returns true or false.
   */
  private function isAdminRoute(RequestEvent $event): bool {
    $route_match = RouteMatch::createFromRequest($event->getRequest());
    if (!$route_match instanceof RouteMatch) {
      return FALSE;
    }
    $route = $route_match->getRouteObject();
    return (bool) $route->getOption('_admin_route');
  }

  /**
   * Check if is default language.
   *
   * @return bool
   *   Returns true or false.
   */
  private function isDefaultLanguage(): bool {
    return $this->languageManager->getCurrentLanguage()->isDefault();
  }

  /**
   * Check if block is on the page.
   *
   * @return bool
   *   Returns true or false.
   */
  private function isBlockOnPage(): bool {
    // Make sure our block is on the page.
    $blocks_per_region = $this->blockRepository->getVisibleBlocksPerRegion();

    return array_reduce($blocks_per_region, function ($carry, $blocks) {
      foreach ($blocks as $block) {
        if ($block->getPluginId() === 'oe_webtools_etrans_lc2023') {
          return TRUE;
        }
      }
      return $carry;
    }, FALSE);
  }

  /**
   * Get redirect URL for european languages.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Event object.
   * @param \Drupal\Core\Language\LanguageInterface $european_lang
   *   European language.
   *
   * @return \Drupal\Core\Url
   *   Returns an URL object.
   */
  private function getRedirectUrl(RequestEvent $event, LanguageInterface $european_lang): Url {
    // Default language will be the default redirect language.
    $redirect_language = $this->languageManager->getDefaultLanguage();
    $pref_user_lang_code = mb_substr($this->languageManager->getCurrentLanguage()
      ->getId(), 0, 2);

    // Get route match from request.
    $route_match = RouteMatch::createFromRequest($event->getRequest());
    $params = $event->getRequest()->query->all();
    $keys_to_remove = ['etransnolive', 'etrans'];
    foreach ($keys_to_remove as $key) {
      if (array_key_exists($key, $params)) {
        unset($params[$key]);
      }
    }
    // Check if we have a route.
    $route = $route_match->getRouteName();
    // Create URL object to redirect to in the correct language.
    if ($route) {
      return Url::fromRoute(
        $route_match->getRouteName(),
        $route_match->getRawParameters()->all(),
        [
          'language' => $redirect_language,
          'query' => array_merge($params, [
            'prefLang' => $pref_user_lang_code,
          ]),
        ],
      );
    }
    // Create from current path.
    $current_path = $event->getRequest()->getPathInfo();
    $path_elements = explode('/', trim($current_path, '/'));
    if ($path_elements[0] === $european_lang->getId()) {
      $path_elements[0] = '/' . $redirect_language->getId();
      return Url::fromUserInput(implode('/', $path_elements), [
        'language' => $redirect_language,
        'query' => array_merge($params, [
          'prefLang' => $pref_user_lang_code,
        ]),
      ]);
    }
    // If we couldn't identify correct path or route, redirect to homepage.
    return Url::fromRoute('<front>', [], [
      'language' => $redirect_language,
    ]);
  }

  /**
   * Set trusted redirect response.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Event object.
   * @param \Drupal\Core\Url $url
   *   Url object.
   * @param \Drupal\Core\Language\LanguageInterface $european_lang
   *   European language object.
   */
  private function setResponse(RequestEvent $event, Url $url, LanguageInterface $european_lang): void {
    // Set the response.
    $cache = new CacheableMetadata();
    $cache->addCacheContexts(['languages', 'url']);
    $cache->addCacheableDependency($european_lang);
    // @todo Make redirect permanent?.
    $response = new TrustedRedirectResponse($url->toString(), '307');
    $response = $this->removeEtransLiveCookieIfDifferent($response, $event, $european_lang->getId());
    $response->addCacheableDependency($cache);
    $event->setResponse($response);
  }

  /**
   * If user changes the language from switcher, remove the "etranslive" cookie.
   *
   * @param \Drupal\Core\Routing\TrustedRedirectResponse $response
   *   Redirect response.
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Event request.
   * @param string $pref_lang_code
   *   Preferred language code.
   *
   * @return mixed
   *   Returns the response.
   */
  private function removeEtransLiveCookieIfDifferent(TrustedRedirectResponse $response, RequestEvent $event, string $pref_lang_code) {
    // Check if the cookie exists.
    if ($event->getRequest()->cookies->has('etranslive')) {
      $cookie = $event->getRequest()->cookies->get('etranslive');
      if (!empty($cookie) && $cookie !== $pref_lang_code) {
        $response->headers->clearCookie('etranslive');
      }
    }
    return $response;
  }

}
