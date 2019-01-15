<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Webtools Analytics rule entities.
 */
interface WebtoolsAnalyticsRuleInterface extends ConfigEntityInterface {

  /**
   * Returns the site section.
   *
   * @return string
   *   Section value for the rule.
   */
  public function getSection(): string;

  /**
   * Indicates if the rule should be applied on the default site language alias.
   *
   * @return bool
   *   True if applies on the default site language alias.
   */
  public function matchOnSiteDefaultLanguage(): bool;

  /**
   * Returns the regular expression.
   *
   * @return string
   *   Regex value for the rule.
   */
  public function getRegex(): string;

}
