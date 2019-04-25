<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules;

use Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface;

/**
 * Interface for services that match routes to Webtools Analytics rules.
 */
interface RuleMatcherInterface {

  /**
   * Returns the Webtools Analytics rule that matches the given path.
   *
   * @param string|null $path
   *   Optional path to match. If omitted the current path will be checked.
   *
   * @return \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface|null
   *   The Webtools Analytics rule entity that matches the given path, or NULL
   *   if no match was found.
   */
  public function getMatchingRule(string $path = NULL): ?WebtoolsAnalyticsRuleInterface;

  /**
   * Returns the site section that matches the given path.
   *
   * @param string|null $path
   *   Optional path to match. If omitted the current path will be checked.
   *
   * @return \Drupal\oe_webtools_analytics_rules\Entity\WebtoolsAnalyticsRuleInterface|null
   *   The site section as returned by the Webtools Analytics rule entity that
   *   matches the given path, or NULL if no match was found.
   */
  public function getMatchingSection(string $path = NULL): ?string;

}
