<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\oe_webtools_analytics\EventSubscriber\AnalyticsEventSubscriber;

/**
 * Test to ensure 'AnalyticsEventSubscriber' service is reachable.
 *
 * @package Drupal\Tests\oe_webtools_analytics\Kernel
 */
class AnalyticsEventSubscriberServiceTest extends KernelTestBase {
  /**
   * {@inheritdoc}
   */
  public static $modules = ['oe_webtools_analytics'];

  /**
   * Test for existence of 'SearchParameters' service.
   */
  public function testWebtoolsImportSettingsService() {
    $subscriber = $this->container->get('oe_webtools_analytics.event_subscriber');
    $this->assertInstanceOf(AnalyticsEventSubscriber::class, $subscriber);
  }

}
