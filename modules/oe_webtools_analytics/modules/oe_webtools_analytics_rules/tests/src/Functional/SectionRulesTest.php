<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics_rules\Functional;

use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests for defining site sections using regular expressions.
 *
 * @group oe_webtools_analytics
 */
class SectionRulesTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'config',
    'system',
    'oe_webtools_analytics_rules',
  ];

  /**
   * Test that sections for different rules are correctly rendered.
   */
  public function testSectionRender(): void {
    // Configure the site to use analytics.
    $config = \Drupal::configFactory()
      ->getEditable(AnalyticsEventInterface::CONFIG_NAME)
      ->set("siteID", "123")
      ->set("sitePath", "ec.europa.eu");
    $config->save();

    $analytic_rules_storage = $this->container->get('entity_type.manager')
      ->getStorage('webtools_analytics_rule');

    // Create first rule under the main administration page.
    $analytic_rules_storage
      ->create([
        'id' => 'id1',
        'section' => 'section1',
        'regex' => '/admin/',
      ])
      ->save();

    // Create a second rule under the main configuration page.
    $analytic_rules_storage
      ->create([
        'id' => 'id2',
        'section' => 'section2',
        'regex' => '/\/admin\/config/',
      ])
      ->save();

    // Frontpage doesn't match any rule so it doesn't render a section.
    $this->drupalGet('<front>');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"]}</script>');

    // The administration page matches the first rule so it renders section1.
    $this->drupalGet('admin');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"siteSection":"section1","is403":true}</script>');

    // The configuration page matches both rules but since they have the same
    // weight, the first rule is applied and section1 is rendered.
    $this->drupalGet('admin/config');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"siteSection":"section1","is403":true}</script>');

    // Change weight of rules.
    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $id2 */
    $id2 = $analytic_rules_storage
      ->load('id1');

    $id2->set('weight', -9);
    $id2->save();

    /** @var \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface $id2 */
    $id2 = $analytic_rules_storage
      ->load('id2');

    $id2->set('weight', -10);
    $id2->save();

    // The configuration page matches both rules but since the second rule
    // is now lighter the second rule is applied and section2 is rendered.
    $this->drupalGet('admin/config');
    $this->assertSession()
      ->responseContains('<script type="application/json">{"utility":"piwik","siteID":"123","sitePath":["ec.europa.eu"],"siteSection":"section2","is403":true}</script>');

  }

}
