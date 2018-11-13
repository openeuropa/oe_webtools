<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\EventSubscriber;

use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for the Webtools Analytics event.
 */
class WebtoolsAnalyticsEventSubscriber implements EventSubscriberInterface {

  /**
   * Webtools Analytics event handler.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   Response event.
   *
   * @throws \RuntimeException
   *   Thrown if storage of webtools_analytics_rule is not available.
   */
  public function setSection(AnalyticsEventInterface $event): void {
    try {
      $storage = \Drupal::entityTypeManager()
        ->getStorage('webtools_analytics_rule');
    }
    catch (\Exception $e) {
      throw new \RuntimeException($e->getMessage());
    }
    $rules = $storage->loadMultiple();
    $current_uri = \Drupal::request()->getRequestUri();
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $rule */
    foreach ($rules as $rule) {
      if (preg_match($rule->getRegex(), $current_uri, $matches) === 1) {
        $event->setSiteSection($rule->getSection());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Analytics event.
    $events[AnalyticsEvent::NAME][] = ['setSection'];

    return $events;
  }

}
