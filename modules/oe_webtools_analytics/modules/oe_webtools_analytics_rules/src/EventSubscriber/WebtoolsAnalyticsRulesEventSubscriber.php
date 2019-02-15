<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\EventSubscriber;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Event subscriber for the Webtools Analytics event.
 */
class WebtoolsAnalyticsRulesEventSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The alias manager service.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * A cache backend interface.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $siteConfig;

  /**
   * WebtoolsAnalyticsEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path service.
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The alias manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend used to store webtools rules for uris.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The Config Factory service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack, CurrentPathStack $currentPath, AliasManagerInterface $aliasManager, CacheBackendInterface $cache, ConfigFactoryInterface $config) {
    $this->entityTypeManager = $entityTypeManager;
    $this->currentRequest = $requestStack->getCurrentRequest();
    $this->currentPath = $currentPath;
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
    $webtools_rules_cache_tags = ['config:webtools_analytics_rule_list'];
    $event->addCacheTags($webtools_rules_cache_tags);

    // Since the rules that are used to discover the site sections are URI based
    // the result cache should vary based on the path.
    $event->addCacheContexts(['url.path']);

    // Getting current path (not system path).
    $current_path = $this->currentRequest->getPathInfo();
    $cache = $this->cache->get($current_path);
    if ($cache && $cache->data === NULL) {
      // If there is no cached data there is no section that applies to the uri.
      return;
    }

    if (isset($cache->data['section'])) {
      $event->setSiteSection($cache->data['section']);
      return;
    }

    $rule = $this->getRuleByPath($current_path);
    if ($rule instanceof WebtoolsAnalyticsRuleInterface) {
      $event->setSiteSection($rule->getSection());
      $this->cache->set($current_path, ['section' => $rule->getSection()], Cache::PERMANENT, $webtools_rules_cache_tags);
      return;
    }

    // Cache NULL if there is no rule that applies to the uri.
    $this->cache->set($current_path, NULL, Cache::PERMANENT, $webtools_rules_cache_tags);
  }

  /**
   * Gets a rule is which related to the current path.
   *
   * @param string $path
   *   Current path.
   *
   * @return \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface|null
   *   Rule related to current path.
   */
  protected function getRuleByPath(string $path): ?WebtoolsAnalyticsRuleInterface {
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface[] $rules */
    $rules = $this->entityTypeManager->getStorage('webtools_analytics_rule')->loadMultiple();

    foreach ($rules as $rule) {
      if ($rule->matchOnSiteDefaultLanguage()) {
        $path = $this->aliasManager->getAliasByPath($this->currentPath->getPath(), $this->siteConfig->get('default_langcode'));
      }

      if (preg_match($rule->getRegex(), $path) === 1) {
        return $rule;
      }
    }

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
