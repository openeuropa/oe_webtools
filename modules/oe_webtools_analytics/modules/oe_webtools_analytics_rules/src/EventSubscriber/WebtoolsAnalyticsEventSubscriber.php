<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Routing\RouteProviderInterface;

/**
 * Event subscriber for the Webtools Analytics event.
 */
class WebtoolsAnalyticsEventSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * The alias manager service.
   *
   * @var \Drupal\Core\Path\AliasManager
   */
  protected $aliasManager;

  /**
   * A cache backend interface.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * The configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  private $siteConfig;

  /**
   * WebtoolsAnalyticsEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The Route provider.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPathStack
   *   The current path service.
   * @param \Drupal\Core\Path\AliasManager $aliasManager
   *   The alias manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend used to store webtools rules for uris.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The Config Factory service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack, RouteProviderInterface $route_provider, CurrentPathStack $currentPathStack, AliasManager $aliasManager, CacheBackendInterface $cache, ConfigFactoryInterface $config) {
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $requestStack;
    $this->routeProvider = $route_provider;
    $this->currentPathStack = $currentPathStack;
    $this->aliasManager = $aliasManager;
    $this->cache = $cache;
    $this->siteConfig = $config->get('system.site');
  }

  /**
   * Webtools Analytics event handler.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   Response event.
   */
  public function analyticsEventHandler(AnalyticsEventInterface $event): void {
    $current_path = $this->requestStack->getCurrentRequest()->getPathInfo();
    if ($cache = $this->cache->get($current_path)) {
      // If there is no cached data there is no section that applies to the uri.
      if ($cache->data === NULL) {
        return;
      }
      // Set site section from the cached data.
      if (isset($cache->data['section'])) {
        $event->setSiteSection($cache->data['section']);
        return;
      }
    }

    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface|false $rule */
    if ($rule = $this->getRuleByPath($current_path)) {
      $event->setSiteSection($rule->getSection());
      $this->cache->set($current_path, ['section' => $rule->getSection()], Cache::PERMANENT, $rule->getCacheTags());
    }

  }

  /**
   * Get a rule which related to current path.
   *
   * @param string $path
   *   Current path.
   *
   * @return \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface|null
   *   Rule related to current path.
   */
  private function getRuleByPath(string $path): ?WebtoolsAnalyticsRuleInterface {
    try {
      $storage = $this->entityTypeManager
        ->getStorage('webtools_analytics_rule');
    }
    // Because of the dynamic nature how entities work in Drupal the entity type
    // manager can throw exceptions if an entity type is not available or
    // invalid. However since we are using our very own entity type we can
    // be certain that this is defined and valid. Convert the exceptions into
    // unchecked runtime exceptions so they don't need to be documented all the
    // way up the call stack.
    catch (InvalidPluginDefinitionException $e) {
      throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
    }
    catch (PluginNotFoundException $e) {
      throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
    }

    $rules = $storage->loadMultiple();
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $rule */
    foreach ($rules as $rule) {
      $current_path = $path;
      if ($rule->matchOnSiteDefaultLanguage()) {
        // Get source of current URI.
        // But some reason we don't have correct information about current path.
        // For updating information
        // we have to run $this->routeProvider->getRouteCollectionForRequest().
        $this->routeProvider->getRouteCollectionForRequest($this->requestStack->getCurrentRequest());
        $current_path = $this->aliasManager->getAliasByPath($this->currentPathStack->getPath(), $this->siteConfig->get('default_langcode'));
      }

      if (preg_match($rule->getRegex(), $current_path) === 1) {
        return $rule;
      }
    }

    // Cache NULL if there is no rule that applies to the uri.
    $this->cache->set($path, NULL, Cache::PERMANENT, $storage->getEntityType()->getListCacheTags());
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Analytics event.
    $events[AnalyticsEvent::NAME][] = ['analyticsEventHandler'];

    return $events;
  }

}
