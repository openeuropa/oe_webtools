<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Provides an interface for the CookieConsentEvent.
 */
interface CookieConsentEventInterface extends RefinableCacheableDependencyInterface {

  /**
   * The CCK configuration name.
   */
  public const CONFIG_NAME = 'oe_webtools_cookie_consent.settings';

  /**
   * Name of the variable in the CCK configuration.
   */
  public const CCK_ENABLED = 'cckEnabled';

  /**
   * Sets whether or not the CCK is enabled.
   *
   * @param bool $cckEnabled
   *   A boolean variable set as true by default.
   */
  public function setCckEnabled(bool $cckEnabled = TRUE): void;

  /**
   * Get whether or not the CCK is enabled.
   *
   * @return bool
   *   True if CCK is enabled.
   */
  public function isCckEnabled(): bool;

}
