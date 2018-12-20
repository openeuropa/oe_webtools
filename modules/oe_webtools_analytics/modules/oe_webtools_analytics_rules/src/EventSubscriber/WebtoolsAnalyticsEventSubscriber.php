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
use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface;
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
    $current_uri = $this->requestStack->getCurrentRequest()->getRequestUri();
    if ($cache = $this->cache->get($current_uri)) {
      if ($cache->data) {
        $event->setSiteSection($cache->data['section']);
      }
      return;
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
        $this->updateAnalyticsEvent($event, $current_uri, $rule);
        // By this break we have to explicitly handle possible overlapping
        // of rules.
        // So for know we will select first suitable rule.
        return;
      }
    }
    // We have to cache for uri NULL data, if we don't have suitable rule.
    $this->cache->set($current_uri, NULL, Cache::PERMANENT, $storage->getEntityType()->getListCacheTags());

  }

  /**
   * Get only needed information from rule.
   *
   * @param \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $rule
   *   Rule config entity.
   *
   * @return array
   *   Structured array of rule
   */
  private function getRuleInfo(WebtoolsAnalyticsRuleInterface $rule): array {
    return [
      'section' => $rule->getSection(),
    ];
  }

  /**
   * Update Webtools Analytics event.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   Response event.
   * @param string $current_uri
   *   Uri of current request.
   * @param \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $rule
   *   Rule config entity.
   */
  private function updateAnalyticsEvent(AnalyticsEventInterface &$event, string $current_uri, WebtoolsAnalyticsRuleInterface $rule): void {
    $rule_data = $this->getRuleInfo();
    $event->setSiteSection($rule_data['section']);
    $this->cache->set($current_uri, $rule_data, Cache::PERMANENT, $rule->getCacheTags());
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
