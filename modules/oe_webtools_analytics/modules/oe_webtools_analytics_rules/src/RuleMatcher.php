<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRule;
use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service that matches routes to rules.
 */
class RuleMatcher implements RuleMatcherInterface {

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
   * Constructs a RuleMatcher service.
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
   * {@inheritdoc}
   */
  public function getMatchingSection(string $path = NULL): ?string {
    $current_path = $this->getCurrentPath();
    $cache = $this->cache->get($current_path) ?: new \stdClass();

    // Check if the cache data for the section has been set, taking into account
    // that its value might be NULL.
    if (empty($cache->data) || !array_key_exists('section', $cache->data)) {
      $rule = $this->getMatchingRule($current_path);

      // Reload the cache object, it is updated when the rule was matched.
      $cache = $this->cache->get($current_path) ?: new \stdClass();

      $section = $rule instanceof WebtoolsAnalyticsRuleInterface ? $rule->getSection() : NULL;
      $cache->data = ['section' => $section] + ($cache->data ?? []);

      // We return results based on rule entities. This means that if a rule is
      // added or deleted, or if any of the existing rules change, the cached
      // results should be invalidated.
      $cache->tags = Cache::mergeTags($cache->tags ?? [], $this->getWebtoolsAnalyticsRuleListCacheTags());

      $this->cache->set($current_path, $cache->data, Cache::PERMANENT, $cache->tags);
    }

    return $cache->data['section'];
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  public function getMatchingRule(string $path = NULL): ?WebtoolsAnalyticsRuleInterface {
    // Default to the current path.
    if (!$path) {
      $path = $this->getCurrentPath();
    }

    $cache = $this->cache->get($path) ?: new \stdClass();
    if (isset($cache->data['rule'])) {
      return $this->loadWebtoolsAnalyticsRule($cache->data['rule']);
    }

    $site_configuration = $this->config->get('system.site');
    $default_language = $site_configuration->get('default_langcode');
    $default_language_alias_path = $this->aliasManager->getAliasByPath($this->currentPath->getPath(), $default_language);

    $matching_rule = NULL;

    foreach ($this->loadWebtoolsAnalyticsRules() as $rule) {
      if (preg_match($rule->getRegex(), $rule->matchOnSiteDefaultLanguage() ? $default_language_alias_path : $path) === 1) {
        $matching_rule = $rule;
        break;
      }
    }

    $rule_id = $matching_rule instanceof WebtoolsAnalyticsRuleInterface ? $matching_rule->id() : NULL;
    $cache->data = ['rule' => $rule_id] + ($cache->data ?? []);

    // We return results based on rule entities. This means that if a rule is
    // added or deleted, or if any of the existing rules change, the cached
    // results should be invalidated.
    $cache->tags = Cache::mergeTags($cache->tags ?? [], $this->getWebtoolsAnalyticsRuleListCacheTags());

    // Add the cache tags of the default site configuration if the rule depends
    // on the default language of the site.
    $default_language_cache_tags = $matching_rule instanceof WebtoolsAnalyticsRuleInterface && $matching_rule->matchOnSiteDefaultLanguage() ? $site_configuration->getCacheTags() : [];
    $cache->tags = Cache::mergeTags($cache->tags, $default_language_cache_tags);

    $this->cache->set($path, $cache->data, Cache::PERMANENT, $cache->tags);

    return $matching_rule;
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
   * Returns the entity storage for the Webtools Analytics Rule entity.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The entity storage.
   */
  protected function getWebtoolsAnalyticsRuleEntityStorage(): EntityStorageInterface {
    try {
      $storage = $this->entityTypeManager->getStorage('webtools_analytics_rule');
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

    return $storage;
  }

  /**
   * Returns the Webtools Analytics Rule entities with the given entity IDs.
   *
   * @param string[]|null $ids
   *   Optional array of entity IDs to return. If omitted all entities will be
   *   returned.
   *
   * @return \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRule[]
   *   The entities.
   */
  protected function loadWebtoolsAnalyticsRules(array $ids = NULL): array {
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRule[] $rules */
    $rules = $this->getWebtoolsAnalyticsRuleEntityStorage()->loadMultiple($ids);
    return $rules;
  }

  /**
   * Returns the Webtools Analytics Rule entity with the given entity ID.
   *
   * @param string $id
   *   The entity ID.
   *
   * @return \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRule|null
   *   The entity, or NULL if no entity with the given ID is found.
   */
  protected function loadWebtoolsAnalyticsRule(string $id): ?WebtoolsAnalyticsRule {
    $rules = $this->loadWebtoolsAnalyticsRules([$id]);
    return reset($rules);
  }

  /**
   * Returns the list cache tags for the Webtools Analytics Rule entity.
   *
   * @return string[]
   *   The list cache tags.
   */
  protected function getWebtoolsAnalyticsRuleListCacheTags(): array {
    return $this->getWebtoolsAnalyticsRuleDefinition()->getListCacheTags();
  }

  /**
   * Returns the current path.
   *
   * @return string
   *   The current path.
   */
  protected function getCurrentPath(): string {
    return $this->requestStack->getCurrentRequest()->getPathInfo();
  }

}
