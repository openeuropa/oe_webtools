<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

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
   * The configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

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
    $this->requestStack = $requestStack;
    $this->currentPath = $currentPath;
    $this->aliasManager = $aliasManager;
    $this->cache = $cache;
    $this->config = $config;
  }

  /**
   * Webtools Analytics event handler.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   The analytics event.
   */
  public function analyticsEventHandler(AnalyticsEventInterface $event): void {
    // We return results based on rule entities. This means that if a rule is
    // added or deleted, or if any of the existing rules change, the cached
    // results should be invalidated.
    $webtools_analytics_rule_definition = $this->getWebtoolsAnalyticsRuleDefinition();
    $webtools_rules_cache_tags = $webtools_analytics_rule_definition->getListCacheTags();
    $event->addCacheTags($webtools_rules_cache_tags);
    $event->addCacheContexts($webtools_analytics_rule_definition->getListCacheContexts());

    // Since the rules that are used to discover the site sections are URI based
    // the result cache should vary based on the path.
    $event->addCacheContexts(['url.path']);

    // Getting current path (not system path).
    $current_path = $this->requestStack->getCurrentRequest()->getPathInfo();
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

    $section = $rule instanceof WebtoolsAnalyticsRuleInterface ? $rule->getSection() : NULL;

    if ($section) {
      $event->setSiteSection($section);
    }

    // Cache NULL if there is no rule that applies to the uri.
    $cache_data = $section ? ['section' => $rule->getSection()] : NULL;
    $this->cache->set($current_path, $cache_data, Cache::PERMANENT, $webtools_rules_cache_tags);
  }

  /**
   * Returns a rule is which related to the given path.
   *
   * @param string $path
   *   The path for which to return the rule.
   *
   * @return \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface|null
   *   The rule related to the given path, or NULL if there is no corresponding
   *   rule.
   */
  protected function getRuleByPath(string $path): ?WebtoolsAnalyticsRuleInterface {
    try {
      /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface[] $rules */
      $rules = $this->entityTypeManager->getStorage('webtools_analytics_rule')->loadMultiple();
    }
    catch (PluginNotFoundException $e) {
      // The entity type manager in core will throw a checked exception if an
      // entity type is not defined. This is intended to deal with situations
      // like the module that defines the entity type not being enabled. In our
      // case we are sure that the entity type exists since we define it in our
      // own module. We can convert this to an unchecked exception so this
      // doesn't need to be checked again higher in the call stack.
      throw new \RuntimeException('The webtools_analytics_rule entity type does not exist.', 0, $e);
    }
    catch (InvalidPluginDefinitionException $e) {
      // The entity type manager in core will throw a checked exception if an
      // entity type is invalid. In our case we are sure that the entity type is
      // valid since we have defined it ourselves. We can convert this to an
      // unchecked exception so this doesn't need to be checked again higher in
      // the call stack.
      throw new \RuntimeException('The webtools_analytics_rule entity type is invalid.', 0, $e);
    }

    $default_language = $this->config->get('system.site')->get('default_langcode');
    $default_language_alias_path = $this->aliasManager->getAliasByPath($this->currentPath->getPath(), $default_language);

    foreach ($rules as $rule) {
      if (preg_match($rule->getRegex(), $rule->matchOnSiteDefaultLanguage() ? $default_language_alias_path : $path) === 1) {
        return $rule;
      }
    }

    return NULL;
  }

  /**
   * Returns the entity type definition for the Webtools Analytics Rule entity.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The entity type definition.
   */
  protected function getWebtoolsAnalyticsRuleDefinition(): EntityTypeInterface {
    try {
      $definition = $this->entityTypeManager->getDefinition('webtools_analytics_rule');
    }
    catch (PluginNotFoundException $e) {
      // The entity type manager in core will throw a checked exception if an
      // entity type is not defined. This is intended to deal with situations
      // like the module that defines the entity type not being enabled. In our
      // case we are sure that the entity type exists since we define it in our
      // own module. We can convert this to an unchecked exception so this
      // doesn't need to be checked again higher in the call stack.
      throw new \RuntimeException('The webtools_analytics_rule entity type does not exist.', 0, $e);
    }

    return $definition;
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
