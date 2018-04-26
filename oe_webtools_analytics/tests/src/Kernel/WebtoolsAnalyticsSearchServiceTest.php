<?php

namespace Drupal\Tests\oe_webtools_analytics\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\oe_webtools_analytics\Entity\WebtoolsAnalyticsSearch;

/**
 * Test to ensure 'WebtoolsAnalyticsSearch' service is reachable.
 *
 * @package Drupal\Tests\oe_webtools_analytics\Kernel
 */
class WebtoolsAnalyticsSearchServiceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['oe_webtools_analytics'];

  /**
   * Test for existence of 'WebtoolsAnalyticsSearch' service.
   */
  public function testWebtoolsAnalyticsSearchService() {
    $subscriber = $this->container->get('oe_webtools_analytics.search');
    $this->assertInstanceOf(WebtoolsAnalyticsSearch::class, $subscriber);
  }

}
