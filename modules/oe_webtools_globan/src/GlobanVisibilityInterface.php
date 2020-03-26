<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_globan;

/**
 * Provides a contract for the 'oe_webtools_goban.visibility' service.
 */
interface GlobanVisibilityInterface {

  /**
   * Checks if the banner should be displayed on the current page.
   *
   * @return bool
   *   If the banner should be displayed on the current page.
   */
  public function shouldDisplayBanner(): bool;

}
