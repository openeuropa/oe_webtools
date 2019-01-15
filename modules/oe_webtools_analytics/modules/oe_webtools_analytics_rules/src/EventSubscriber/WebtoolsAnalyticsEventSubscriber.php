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

/**
 * Event subscriber for the Webtools Analytics event.
 */
class WebtoolsAnalyticsEventSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path service.
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The alias manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend used to store webtools rules for uris.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The Config Factory service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, CurrentPathStack $currentPath, AliasManagerInterface $aliasManager, CacheBackendInterface $cache, ConfigFactoryInterface $config) {
    $this->entityTypeManager = $entityTypeManager;
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
    $event->addCacheTags(['webtools_analytics_rule_list']);
    $current_path = $this->currentPath->getPath();
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
      $this->cache->set($current_path, ['section' => $rule->getSection()], Cache::PERMANENT, $rule->getCacheTags());
      return;
    }

    // Cache NULL if there is no rule that applies to the uri.
    $this->cache->set($current_path, NULL, Cache::PERMANENT, ['webtools_analytics_rule_list']);
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
  protected function getRuleByPath(string $path): ?WebtoolsAnalyticsRuleInterface {
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface[] $rules */
    $rules = $this->entityTypeManager->getStorage('webtools_analytics_rule')->loadMultiple();

    foreach ($rules as $rule) {
      $current_path = $path;
      if ($rule->matchOnSiteDefaultLanguage()) {
        $current_path = $this->aliasManager->getAliasByPath($this->currentPath->getPath(), $this->siteConfig->get('default_langcode'));
      }

      if (preg_match($rule->getRegex(), $current_path) === 1) {
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
