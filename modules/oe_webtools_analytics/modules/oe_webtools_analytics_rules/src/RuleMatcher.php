<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRule;
use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service that matches routes to rules.
 */
class RuleMatcher implements RuleMatcherInterface {

  /**
   * The alias manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
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
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

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
   * Constructs a RuleMatcher service.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   The alias manager service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend used to store webtools rules for uris.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The Config Factory service.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   The current path service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(AliasManagerInterface $aliasManager, CacheBackendInterface $cache, ConfigFactoryInterface $config, CurrentPathStack $currentPath, EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack) {
    $this->aliasManager = $aliasManager;
    $this->cache = $cache;
    $this->config = $config;
    $this->currentPath = $currentPath;
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public function getMatchingSection(string $path = NULL): ?string {
    return $this->getDataForPath($path)['section'];
  }

  /**
   * {@inheritdoc}
   */
  public function getMatchingRule(string $path = NULL): ?WebtoolsAnalyticsRuleInterface {
    $rule_id = $this->getDataForPath($path)['rule'];
    return !empty($rule_id) ? $this->loadRule($rule_id) : NULL;
  }

  /**
   * Returns cached analytics rule data for the given path.
   *
   * @param string|null $path
   *   Optional path for which to return data. If omitted the current path will
   *   be used.
   *
   * @return array
   *   An associative array with two keys:
   *   - section: Optional section name that matches the given path, or NULL if
   *     there is no matching section.
   *   - rule: Optional ID of the Webtools Analytics Rule entity that was used
   *     to generate the matching section, or NULL if there is no matchine rule.
   */
  protected function getDataForPath(string $path = NULL): array {
    // Default to the current path.
    if (!$path) {
      $path = $this->getCurrentPath();
    }

    $cache = $this->cache->get($path) ?: new \stdClass();

    // Return cached data if it exists.
    if (empty($cache->data)) {
      $cache = $this->populateCache($path);
    }

    return $cache->data;
  }

  /**
   * Generates a fresh cache entry for the given path.
   *
   * @param string $path
   *   The path for which to refresh the cache.
   *
   * @return object
   *   The cache entry that was generated.
   */
  protected function populateCache(string $path): \stdClass {
    $data = ['rule' => NULL, 'section' => NULL];

    $expire = Cache::PERMANENT;

    // We return results based on rule entities. This means that if a rule is
    // added or deleted, or if any of the existing rules change, the cached
    // results should be invalidated.
    $tags = $this->getListCacheTags();

    if ($rule = $this->findMatchingRule($path)) {
      $data['rule'] = $rule->id();
      $data['section'] = $rule->getSection();

      // Add the cache tags of the default site configuration if the rule
      // depends on the default language of the site.
      if ($rule->matchOnSiteDefaultLanguage()) {
        $tags = Cache::mergeTags($tags, $this->getSiteConfig()->getCacheTags());
      }
    }

    $this->cache->set($path, $data, $expire, $tags);

    return (object) ['data' => $data, 'expire' => $expire, 'tags' => $tags];
  }

  /**
   * Loops over the available rules and returns the first matching rule.
   *
   * @param string $path
   *   The path to match.
   *
   * @return \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface|null
   *   The first matching rule, or NULL if none of the available rules match the
   *   given path.
   */
  protected function findMatchingRule(string $path): ?WebtoolsAnalyticsRuleInterface {
    $site_configuration = $this->getSiteConfig();
    $default_language = $site_configuration->get('default_langcode');
    $default_language_alias_path = $this->aliasManager->getAliasByPath($this->currentPath->getPath(), $default_language);

    foreach ($this->loadRules() as $rule) {
      if (preg_match($rule->getRegex(), $rule->matchOnSiteDefaultLanguage() ? $default_language_alias_path : $path) === 1) {
        return $rule;
      }
    }

    return NULL;
  }

  /**
   * Returns the default site configuration.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The configuration object for the default site configuration.
   */
  protected function getSiteConfig() {
    return $this->config->get('system.site');
  }

  /**
   * Returns the entity type definition for the Webtools Analytics Rule entity.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The entity type definition.
   */
  protected function getRuleDefinition(): EntityTypeInterface {
    return $definition = $this->entityTypeManager->getDefinition('webtools_analytics_rule');
  }

  /**
   * Returns the entity storage for the Webtools Analytics Rule entity.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   The entity storage.
   */
  protected function getRuleEntityStorage(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('webtools_analytics_rule');
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
  protected function loadRules(array $ids = NULL): array {
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRule[] $rules */
    $rules = $this->getRuleEntityStorage()->loadMultiple($ids);

    if (count($rules) > 1) {
      uasort($rules, function (WebtoolsAnalyticsRuleInterface $a, WebtoolsAnalyticsRuleInterface $b) {
        return ($a->get('weight') <=> $b->get('weight'));
      });
    }

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
  protected function loadRule(string $id): ?WebtoolsAnalyticsRule {
    $rules = $this->loadRules([$id]);
    return reset($rules);
  }

  /**
   * Returns the list cache tags for the Webtools Analytics Rule entity.
   *
   * @return string[]
   *   The list cache tags.
   */
  protected function getListCacheTags(): array {
    return $this->getRuleDefinition()->getListCacheTags();
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
