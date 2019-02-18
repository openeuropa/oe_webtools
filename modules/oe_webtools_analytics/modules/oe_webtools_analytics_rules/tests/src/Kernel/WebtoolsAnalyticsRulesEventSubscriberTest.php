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
   * An array of test path aliases in different languages, keyed by system path.
   */
  const PATH_ALIASES = [
    '/news_overview_page' => [
      'en' => '/news',
      'es' => '/nuevas',
    ],
    '/taxonomy/term/344' => [
      'en' => '/news/antarctica',
      'es' => '/es/nuevas/antartida',
    ],
    // The alias in the English language has been omitted so we can test that
    // the rules still work if aliases can be auto-generated using modules like
    // Pathauto.
    '/articles_page' => [
      'es' => '/es/articulos',
    ],
  ];

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
   * The path alias manager used for testing.
   *
   * @var \Drupal\Tests\oe_webtools_analytics_rules\Kernel\MockAliasManager
   */
  protected $aliasManager;

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

    // Use the mock alias manager in the container.
    $this->aliasManager = new MockAliasManager();
    $this->container->set('path.alias_manager', $this->aliasManager);

    $this->initializeEvent();
    $this->eventSubscriber = $this->container->get('oe_webtools_analytics_rules.event_subscriber');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->currentPathStack = $this->container->get('path.current');
    $this->requestStack = $this->container->get('request_stack');
    $this->ruleEntityType = $this->entityTypeManager->getDefinition('webtools_analytics_rule');
    $this->ruleEntityStorage = $this->entityTypeManager->getStorage('webtools_analytics_rule');

    $this->createPathAliases();
  }

  /**
   * Tests the event subscriber that provides rule based sections to analytics.
   *
   * @param array[] $rules_data
   *   An associative array of data to use to create test rules, keyed by rule
   *   ID. The data is an associative array with the following keys:
   *   - section: the site section that the rule returns on a successful match.
   *   - regex: the regular expression used to perform the matching magic.
   *   - match_on_site_default_language: whether or not the rule is intended to
   *     perform the matching on paths in the default language of the site.
   * @param string[] $expected_sections_by_language
   *   An associative array, keyed by the default language to use in the test.
   *   Each value is an array of sections that are expected to be returned for
   *   the paths that are used as array keys. If the value is an empty string
   *   this indicates that no section is expected to match.
   *
   * @dataProvider eventSubscriberProvider
   */
  public function testEventSubscriber(array $rules_data, array $expected_sections_by_language): void {
    $this->createRules($rules_data);

    // Set the default language to use during the test.
    foreach ($expected_sections_by_language as $language => $expected_sections) {
      $this->config('system.site')->set('default_langcode', $language)->save();

      // Check that the expected sections are returned for the given test paths.
      foreach ($expected_sections as $path => $expected_section) {
        // Start with a clean event for each test case.
        $this->initializeEvent();

        // Set the current path to the one being tested.
        $this->setCurrentPath($path);

        // Let the subscriber perform its magic.
        $this->invokeAnalyticsEvent();

        // Check that the expected section is set on the event.
        $this->assertEquals($expected_section, $this->event->getSiteSection(), "The path '$path' is expected to have the site section '$expected_section' when the default language is set to '$language'.");

        // Since the rules that are used to discover the site sections are URI
        // based the result cache should vary based on the path.
        $this->assertCacheContexts(['url.path']);

        // If any of the rules change then the result cache should be
        // invalidated. Check that the list cache tags and contexts of the rule
        // entity are included in the result.
        $this->assertCacheContexts($this->ruleEntityType->getListCacheContexts());
        $this->assertCacheTags($this->ruleEntityType->getListCacheTags());
      }
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
          'en' => [
            '/' => '',
            '/admin' => '',
            '/admin/config' => '',
            '/admin/config/system' => '',
            '/admin/config/system/site-information' => '',
            '/admin/structure' => '',
            '/admin/structure/block' => '',
            '/news_overview_page' => '',
            '/news' => '',
            '/nuevas' => '',
            '/taxonomy/term/344' => '',
            '/news/antarctica' => '',
            '/es/nuevas/antartida' => '',
            '/articles_page' => '',
            '/es/articulos' => '',
          ],
          'es' => [
            '/' => '',
            '/admin' => '',
            '/admin/config' => '',
            '/admin/config/system' => '',
            '/admin/config/system/site-information' => '',
            '/admin/structure' => '',
            '/admin/structure/block' => '',
            '/news_overview_page' => '',
            '/news' => '',
            '/nuevas' => '',
            '/taxonomy/term/344' => '',
            '/news/antarctica' => '',
            '/es/nuevas/antartida' => '',
            '/articles_page' => '',
            '/es/articulos' => '',
          ],
        ],
      ],
      // Test two rules, one for the configuration section, and one for the
      // site structure section.
      [
        [
          'config' => [
            'section' => 'site configuration',
            'regex' => '|^/admin/config/?.*|',
            'match_on_site_default_language' => FALSE,
          ],
          'structure' => [
            'section' => 'site structure',
            'regex' => '|^/admin/structure/?.*|',
            'match_on_site_default_language' => FALSE,
          ],
        ],
        [
          'en' => [
            '/' => '',
            '/admin' => '',
            '/admin/config' => 'site configuration',
            '/admin/config/system' => 'site configuration',
            '/admin/config/system/site-information' => 'site configuration',
            '/admin/structure' => 'site structure',
            '/admin/structure/block' => 'site structure',
            '/some/other/admin/config/' => '',
            '/a/non/matching/admin/structure/' => '',
          ],
          'es' => [
            '/' => '',
            '/admin' => '',
            '/admin/config' => 'site configuration',
            '/admin/config/system' => 'site configuration',
            '/admin/config/system/site-information' => 'site configuration',
            '/admin/structure' => 'site structure',
            '/admin/structure/block' => 'site structure',
            '/some/other/admin/config/' => '',
            '/a/non/matching/admin/structure/' => '',
          ],
        ],
      ],
      // Test a combination of rules that match on the default language and the
      // current language, with two different default languages.
      // Note that the rule IDs are prefixed with numbers. This is because they
      // are currently executed in alphabetical order.
      // @todo Replace the number prefixes with priorities once OPENEUROPA-1633
      //   is fixed.
      // @see https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1633
      [
        [
          // The rule to match the news overview on the default language alias
          // when the default language is set to English.
          '0_news_overview_default_language_alias_english' => [
            'section' => 'news overview (default language alias)',
            'regex' => '|^/news/?$|',
            'match_on_site_default_language' => TRUE,
          ],
          // The rule to match the news overview on the default language alias
          // when the default language is set to Spanish.
          '1_news_overview_default_language_alias_spanish' => [
            'section' => 'news overview (default language alias)',
            'regex' => '|^/nuevas/?$|',
            'match_on_site_default_language' => TRUE,
          ],
          // A rule that checks if the current path matches a regular expression
          // for the system path of the Antarctican news overview page. Since
          // this appears earlier in the database than the following rule this
          // will take precedence over it.
          // @todo The order of rules should be handled with a configurable
          //   priority.
          // @see https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1633
          '2_antarctican_news_overview_current_path' => [
            'section' => 'overview of antarctican news (current path)',
            'regex' => '|^/taxonomy/term/344/?$|',
            'match_on_site_default_language' => FALSE,
          ],
          // The Antarctican news overview page set up to match the default
          // language alias in English.
          '3_antarctican_news_overview_default_language_alias_english' => [
            'section' => 'overview of antarctican news (default language alias)',
            'regex' => '|^/news/antarctica/?$|',
            'match_on_site_default_language' => TRUE,
          ],
          // The Antarctican news overview page set up to match the default
          // language alias in Spanish.
          '4_antarctican_news_overview_default_language_alias_spanish' => [
            'section' => 'overview of antarctican news (default language alias)',
            'regex' => '|^/es/nuevas/antartida/?$|',
            'match_on_site_default_language' => TRUE,
          ],
          // The articles overview set up to match the default language alias
          // in English. Note that the English alias has not been created. This
          // should still be possible to match if the Pathauto module is
          // enabled and OPENEUROPA-1637 is fixed.
          '5_articles_overview_default_language_alias_english' => [
            'section' => 'overview of articles (default language alias)',
            'regex' => '|^/articles/?$|',
            'match_on_site_default_language' => TRUE,
          ],
          // The articles overview matching the current path with a regex that
          // looks for the system path. This has been defined to have a lower
          // priority than the rules that match the default site aliases.
          '6_articles_overview_current_path' => [
            'section' => 'overview of articles (current path)',
            'regex' => '|^/articles_page/?$|',
            'match_on_site_default_language' => FALSE,
          ],
        ],
        [
          'en' => [
            // Since an alias in English exists for the news overview page, this
            // will match for all paths, including the system path and
            // translations.
            '/news_overview_page' => 'news overview (default language alias)',
            '/news' => 'news overview (default language alias)',
            '/nuevas' => 'news overview (default language alias)',
            // The definition of the unaliased system path is present earlier in
            // the database and takes precedence.
            // @todo The order of rules should be handled with a configurable
            //   priority.
            // @see https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1633
            '/taxonomy/term/344' => 'overview of antarctican news (current path)',
            '/news/antarctica' => 'overview of antarctican news (default language alias)',
            '/es/nuevas/antartida' => 'overview of antarctican news (default language alias)',
            // This currently matches on the system path instead of on the
            // default language alias since there is no alias defined in English
            // (which is the default language).
            // @todo Update this one OPENEUROPA-1637 is fixed.
            //   Once this is fixed this is expected to match on the default
            //   language alias since this rule has a higher priority than the
            //   system path rule. This will require a dependency on the
            //   Pathauto module.
            // @see https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1637
            '/articles_page' => 'overview of articles (current path)',
            // This is expected to match on the overview of articles in the
            // default language alias but this is currently not working because
            // the alias in English doesn't exist.
            // @todo Update this once OPENEUROPA-1637 is fixed.
            // @see https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1637
            '/es/articulos' => '',
          ],
          'es' => [
            // Since an alias in English exists for the news overview page, this
            // will match for all paths, including the system path and
            // translations.
            '/news_overview_page' => 'news overview (default language alias)',
            '/news' => 'news overview (default language alias)',
            '/nuevas' => 'news overview (default language alias)',
            // The definition of the unaliased system path is present earlier in
            // the database and takes precedence.
            // @todo The order of rules should be handled with a configurable
            //   priority.
            // @see https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1633
            '/taxonomy/term/344' => 'overview of antarctican news (current path)',
            '/news/antarctica' => 'overview of antarctican news (default language alias)',
            '/es/nuevas/antartida' => 'overview of antarctican news (default language alias)',
            // This doesn't match on the system path but on the default language
            // alias since this rule has a higher priority.
            '/articles_page' => 'overview of articles (current path)',
            // There is no rule to define the default language alias when the
            // site language is set to Spanish, but it should still be possible
            // to match this using a rule that resolves the system path.
            // @todo Update this once OPENEUROPA-1636 is fixed.
            // @see https://webgate.ec.europa.eu/CITnet/jira/browse/OPENEUROPA-1636
            '/es/articulos' => '',
          ],
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
        'match_on_site_default_language' => $rule_data['match_on_site_default_language'],
      ])->save();
    }
  }

  /**
   * Creates a number of path aliases to use in the test.
   */
  protected function createPathAliases(): void {
    foreach (static::PATH_ALIASES as $path => $aliases) {
      foreach ($aliases as $langcode => $alias) {
        $this->aliasManager->addAlias($path, $alias, $langcode);
      }
    }
  }

}
