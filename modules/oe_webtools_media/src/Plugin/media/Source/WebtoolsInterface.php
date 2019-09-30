<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\media\Source;

use Drupal\media\MediaSourceFieldConstraintsInterface;

/**
 * Defines additional functionality for source plugins that use webtools.
 */
interface WebtoolsInterface extends MediaSourceFieldConstraintsInterface {

  /**
   * Returns the webtools widget types.
   *
   * @return array
   *   The widget types.
   */
  public function getWidgetTypes();

}
