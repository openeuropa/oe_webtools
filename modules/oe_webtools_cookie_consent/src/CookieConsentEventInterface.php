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
  public const BANNER_POPUP = 'banner_popup';

  /**
   * Name of the variable in the CCK configuration.
   */
  public const MEDIA_OEMBED_POPUP = 'media_oembed_popup';

  /**
   * Sets whether or not the CCK is enabled.
   *
   * @param bool $bannerPopup
   *   A boolean variable set as true by default.
   */
  public function setBannerPopup(bool $bannerPopup): void;

  /**
   * Sets whether or not the CCK is enabled.
   *
   * @param bool $mediaOembedPopup
   *   A boolean variable set as true by default.
   */
  public function setMediaOembedPopup(bool $mediaOembedPopup): void;

  /**
   * Get whether or not the CCK is enabled.
   *
   * @return bool
   *   True if CCK is enabled.
   */
  public function isBannerPopup(): bool;

  /**
   * Get whether or not the CCK is enabled.
   *
   * @return bool
   *   True if CCK is enabled.
   */
  public function isMediaOembedPopup(): bool;

}
