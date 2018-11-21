<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Drupal\oe_webtools_analytics_rules\EventSubscriber\WebtoolsAnalyticsEventSubscriber;

/**
 * Defines the WorkspaceCacheContext service, for "per workspace" caching.
 *
 * Cache context ID: 'webtools_analytics_section'.
 */
class OpenEuropaWebtoolsAnalyticsSiteSectionCacheContext implements CacheContextInterface {

  /**
   * The Webtools Analytics event subscriber.
   *
   * @var \Drupal\workspaces\WorkspaceManagerInterface
   */
  protected $analyticsEventSubscriber;

  /**
   * Constructs a OpenEuropaWebtoolsAnalyticsSiteSectionCacheContext service.
   *
   * @param \Drupal\oe_webtools_analytics_rules\EventSubscriber\WebtoolsAnalyticsEventSubscriber $analytics_event_subscriber
   *   The workspace manager.
   */
  public function __construct(WebtoolsAnalyticsEventSubscriber $analytics_event_subscriber) {
    $this->analyticsEventSubscriber = $analytics_event_subscriber;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Webtools Analytics site section');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $event = new AnalyticsEvent();
    $this->analyticsEventSubscriber->setSection($event);

    return $event->getSiteSection();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($type = NULL) {
    return new CacheableMetadata();
  }

}
