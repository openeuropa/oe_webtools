<?php

declare(strict_types = 1);

/**
 * Contains the interface that will represent the dimensions on AnalyticsEvent.
 *
 * @see https://webgate.ec.europa.eu/fpfis/wikis/pages/viewpage.action?spaceKey=webtools&title=Piwik
 */

namespace Drupal\oe_webtools_analytics;

/**
 * The interface for custom dimensions support.
 *
 * @package Drupal\oe_webtools_analytics
 */
interface CustomDimensionsInterface {

  /**
   * Gets the custom dimensions.
   *
   * @return array
   *   The array with custom dimensions.
   */
  public function getDimensions(): array;

  /**
   * Add the custom dimension.
   *
   * @param int $id
   *   The ID of dimension.
   * @param string $value
   *   The value of dimension.
   */
  public function addDimension(int $id, string $value): void;

  /**
   * Has custom dimensions.
   *
   * @return bool
   *   True in case the variable has been set otherwise false.
   */
  public function hasCustomDimensions(): bool;

}
