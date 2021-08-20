<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_page_feedback\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Invalidates cache tags whenever configuration for the webtools DFF changes.
 */
class PageFeedbackConfigCacheInvalidator implements EventSubscriberInterface {

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a PageFeedbackConfigCacheInvalidator object.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   */
  public function __construct(CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * Invalidate cache tags when page feedback form config object changes.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The event to process.
   */
  public function onChange(ConfigCrudEvent $event) {
    if ($event->getConfig()->getName() === 'oe_webtools_page_feedback.settings') {
      $this->cacheTagsInvalidator->invalidateTags(['library_info', 'rendered']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[ConfigEvents::SAVE][] = ['onChange'];
    $events[ConfigEvents::DELETE][] = ['onChange'];

    return $events;
  }

}
