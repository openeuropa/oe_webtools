<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics_rules\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Drupal\oe_webtools_analytics_rules\EventSubscriber\WebtoolsAnalyticsRulesEventSubscriber;

/**
 * Tests that rule based analytics sections are returned for the current path.
 *
 * @group oe_webtools_analytics_rules
 */
class WebtoolsAnalyticsRulesEventSubscriberTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_webtools',
    'oe_webtools_analytics',
    'oe_webtools_analytics_rules',
  ];

  /**
   * The analytics event object to use in the test.
   *
   * @var \Drupal\oe_webtools_analytics\AnalyticsEventInterface
   */
  protected $event;

  /**
   * The event subscriber. This is the system under test.
   *
   * @var \Drupal\oe_webtools_analytics_rules\EventSubscriber\WebtoolsAnalyticsRulesEventSubscriber
   */
  protected $eventSubscriber;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->event = new AnalyticsEvent();
    $this->eventSubscriber = $this->container->get('oe_webtools_analytics_rules.event_subscriber');
  }

  /**
   * Tests the event subscriber that provides rule based sections to analytics.
   */
  public function testEventSubscriber(): void {
    $this->invokeAnalyticsEvent();

    // Since the rules that are used to discover the site sections are URI based
    // the result cache should vary based on the path.
    $cache_contexts = $this->event->getCacheContexts();
    $this->assertTrue(in_array('url.path', $cache_contexts));
  }

  /**
   * Invokes the analytics event on the event handler.
   *
   * This invokes the main public method on the event subscriber under test.
   */
  protected function invokeAnalyticsEvent(): void {
    $this->eventSubscriber->analyticsEventHandler($this->event);
  }

}
