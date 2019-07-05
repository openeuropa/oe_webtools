<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Provides an interface for CookieConsentEvent.
 */
interface CookieConsentEventInterface extends RefinableCacheableDependencyInterface {

  /**
   * The Webtools Cookie Consent entrance in settings.php.
   */
  public const CONFIG_NAME = 'oe_webtools_cookie_consent.settings';
  /**
   * The site unique identifier.
   */
  public const CCK_ENABLED = 'cckEnabled';

  /**
   * Sets to true on CCK enabled.
   *
   * @param bool $cckEnabled
   *   A boolean variable set as true by default.
   */
  public function setCckEnabled(bool $cckEnabled = TRUE): void;

  /**
   * Get whether or not is CCK is enabled.
   *
   * @return bool
   *   True in case is CCK is enabled.
   */
  public function isCckEnabled(): bool;

}
