<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics_rules\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Symfony\Component\HttpFoundation\Request;

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The class representing the current path used in the test.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity type definition of the Webtools Analytics Rule entity.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $ruleEntityType;

  /**
   * The entity storage for the Webtools Analytics Rule entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ruleEntityStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->initializeEvent();
    $this->eventSubscriber = $this->container->get('oe_webtools_analytics_rules.event_subscriber');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->currentPathStack = $this->container->get('path.current');
    $this->requestStack = $this->container->get('request_stack');
    $this->ruleEntityType = $this->entityTypeManager->getDefinition('webtools_analytics_rule');
    $this->ruleEntityStorage = $this->entityTypeManager->getStorage('webtools_analytics_rule');
  }

  /**
   * Tests the event subscriber that provides rule based sections to analytics.
   *
   * @param array[] $rules_data
   *   An associative array of data to use to create test rules, keyed by rule
   *   ID. The data is an associative array with the following keys:
   *   - section: the site section that the rule returns on a successful match.
   *   - regex: the regular expression used to perform the matching magic.
   * @param string[] $expected_sections
   *   An associative array of expecting sections that should be returned for
   *   the paths that are used as array keys. If the value is an empty string
   *   this indicates that no section is expected to match.
   *
   * @dataProvider eventSubscriberProvider
   */
  public function testEventSubscriber(array $rules_data, array $expected_sections): void {
    $this->createRules($rules_data);

    // Check that the expected sections are returned for the given test paths.
    foreach ($expected_sections as $path => $expected_section) {
      // Start with a clean event for each test case.
      $this->initializeEvent();

      // Set the current path to the one being tested.
      $this->setCurrentPath($path);

      // Let the subscriber perform its magic.
      $this->invokeAnalyticsEvent();

      // Check that the expected section is set on the event.
      $this->assertEquals($expected_section, $this->event->getSiteSection(), "The path '$path' is expected to have the site section '$expected_section'.");

      // Since the rules that are used to discover the site sections are URI
      // based the result cache should vary based on the path.
      $this->assertCacheContexts(['url.path']);

      // If any of the rules change then the result cache should be invalidated.
      // Check that the list cache tags and contexts of the rule entity are
      // included in the result.
      $this->assertCacheContexts($this->ruleEntityType->getListCacheContexts());
      $this->assertCacheTags($this->ruleEntityType->getListCacheTags());
    }
  }

  /**
   * Returns test data for ::testEventSubscriber().
   */
  public function eventSubscriberProvider(): array {
    return [
      // When no rules are defined it is expected that none of the paths return
      // sections.
      [
        [],
        [
          '/' => '',
          '/admin' => '',
          '/admin/config' => '',
          '/admin/config/system' => '',
          '/admin/config/system/site-information' => '',
          '/admin/structure' => '',
          '/admin/structure/block' => '',
        ],
      ],
      // Test two rules, one for the configuration section, and one for the
      // site structure section.
      [
        [
          'config' => [
            'section' => 'site configuration',
            'regex' => '|^/admin/config|',
          ],
          'structure' => [
            'section' => 'site structure',
            'regex' => '|^/admin/structure|',
          ],
        ],
        [
          '/' => '',
          '/admin' => '',
          '/admin/config' => 'site configuration',
          '/admin/config/system' => 'site configuration',
          '/admin/config/system/site-information' => 'site configuration',
          '/admin/structure' => 'site structure',
          '/admin/structure/block' => 'site structure',
          '/some/other/admin/config/' => '',
        ],
      ],
    ];
  }

  /**
   * Checks that the given cache contexts are present on the event.
   *
   * @param string[] $contexts
   *   The contexts to check.
   */
  protected function assertCacheContexts(array $contexts): void {
    $actual_cache_contexts = $this->event->getCacheContexts();
    foreach ($contexts as $context) {
      $this->assertTrue(in_array($context, $actual_cache_contexts), "The '$context' cache context is present on the event.");
    }
  }

  /**
   * Checks that the given cache tags are present on the event.
   *
   * @param string[] $tags
   *   The cache tags to check.
   */
  protected function assertCacheTags(array $tags): void {
    $actual_cache_tags = $this->event->getCacheTags();
    foreach ($tags as $tag) {
      $this->assertTrue(in_array($tag, $actual_cache_tags), "The '$tag' cache tag is present on the event.");
    }
  }

  /**
   * Invokes the analytics event on the event handler.
   *
   * This invokes the main public method on the event subscriber under test.
   */
  protected function invokeAnalyticsEvent(): void {
    $this->eventSubscriber->analyticsEventHandler($this->event);
  }

  /**
   * Initializes a new analytics event to use in the test.
   */
  protected function initializeEvent(): void {
    $this->event = new AnalyticsEvent();
  }

  /**
   * Sets the current path on the request.
   *
   * @param string $path
   *   The path to set.
   */
  protected function setCurrentPath(string $path): void {
    $this->requestStack->push(Request::create($path));
    $this->currentPathStack->setPath($path);
  }

  /**
   * Creates analytics rules from the given data.
   *
   * @param array[] $rules_data
   *   An associative array of data to use to create test rules, keyed by rule
   *   ID. The data is an associative array with the following keys:
   *   - section: the site section that the rule returns on a successful match.
   *   - regex: the regular expression used to perform the matching magic.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown when a rule cannot be saved.
   */
  protected function createRules(array $rules_data): void {
    foreach ($rules_data as $id => $rule_data) {
      $this->ruleEntityStorage->create([
        'id' => $id,
        'section' => $rule_data['section'],
        'regex' => $rule_data['regex'],
      ])->save();
    }
  }

}
