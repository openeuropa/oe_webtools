<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_globan\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Invalidates cache tags whenever configuration for the global banner changes.
 */
class GlobanConfigCacheInvalidator implements EventSubscriberInterface {

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a GlobanConfigCacheInvalidator object.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   */
  public function __construct(CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * Invalidate cache tags when globan config object changes.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The Event to process.
   */
  public function onChange(ConfigCrudEvent $event) {
    if ($event->getConfig()->getName() === 'oe_webtools_globan.settings') {
      $this->cacheTagsInvalidator->invalidateTags(['library_info']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::SAVE][] = ['onChange'];
    $events[ConfigEvents::DELETE][] = ['onChange'];

    return $events;
  }

}
