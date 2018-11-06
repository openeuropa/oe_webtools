<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics_rules\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Webtools Analytics rule entities.
 */
interface WebtoolsAnalyticsRuleInterface extends ConfigEntityInterface {

  /**
   * Getter for section.
   *
   * @return string
   *   Section value for the rule.
   */
  public function getSection();

  /**
   * Getter for regex.
   *
   * @return string
   *   Regex value for the rule.
   */
  public function getRegex();

}
