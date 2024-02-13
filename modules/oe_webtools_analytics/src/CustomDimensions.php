<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_analytics;

/**
 * Provides the custom dimension parameters.
 *
 * @see https://webgate.ec.europa.eu/fpfis/wikis/pages/viewpage.action?spaceKey=webtools&title=Piwik
 *
 * @package Drupal\oe_webtools_analytics
 */
class CustomDimensions implements CustomDimensionsInterface {

  /**
   * Custom dimensions.
   *
   * @var array
   *   The array with custom dimensions.
   */
  private $dimensions;

  /**
   * {@inheritdoc}
   */
  public function getDimensions(): array {
    return $this->dimensions ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function addDimension(int $id, string $value): void {
    $this->dimensions[] = [
      'id' => $id,
      'value' => $value,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function hasCustomDimensions(): bool {
    return !empty($this->getDimensions());
  }

}
