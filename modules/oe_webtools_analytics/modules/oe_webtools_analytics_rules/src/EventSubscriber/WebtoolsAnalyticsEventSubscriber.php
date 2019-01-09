<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\EventSubscriber;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * A cache backend interface.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * WebtoolsAnalyticsEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   A cache backend used to store webtools rules for uris.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack, CacheBackendInterface $cache) {
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $requestStack;
    $this->cache = $cache;
  }

  /**
   * Webtools Analytics event handler.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   Response event.
   */
  public function analyticsEventHandler(AnalyticsEventInterface $event): void {
    // We need to invalidate the render arrays if any rule changes.
    $event->addCacheTags(['webtools_analytics_rule_list']);
    $current_uri = $this->requestStack->getCurrentRequest()->getRequestUri();
    if ($cache = $this->cache->get($current_uri)) {
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
      if (preg_match($rule->getRegex(), $current_uri, $matches) === 1) {
        $event->setSiteSection($rule->getSection());
        $this->cache->set($current_uri, ['section' => $rule->getSection()], Cache::PERMANENT, $rule->getCacheTags());
        // Currently there is no defined behavior for overlapping rules so we
        // only take into account the first rule that applies.
        return;
      }
    }
    // Cache NULL if there is no rule that applies to the uri.
    $this->cache->set($current_uri, NULL, Cache::PERMANENT, $storage->getEntityType()->getListCacheTags());
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
