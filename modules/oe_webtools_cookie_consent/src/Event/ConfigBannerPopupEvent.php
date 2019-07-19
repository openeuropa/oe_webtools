<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\Event;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event fired when a page is displayed, in order to handle Cookie Consent data.
 *
 * @see oe_webtools_cookie_consent_page_attachments()
 */
class ConfigBannerPopupEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * This event allows you to set the Cookie Consent variable.
   *
   * @Event Drupal\oe_webtools_cookie_consent\Event\WebtoolsImportDataEvent
   */
  public const NAME = 'oe_webtools_cookie_consent.data_collection_banner_popup';

  /**
   * Whether the banner CCK loader is enabled or not.
   *
   * @var bool
   */
  protected $bannerPopup = TRUE;

  /**
   * ConfigBannerPopupEvent constructor.
   */
  public function __construct() {
    $this->setBannerPopup();
  }

  /**
   * Set whether or not the banner popup is enabled.
   *
   * @param bool $bannerPopup
   *   A boolean variable set as true by default.
   */
  public function setBannerPopup(bool $bannerPopup = TRUE): void {
    $this->bannerPopup = $bannerPopup;
  }

  /**
   * Get whether or not the banner popup is enabled.
   *
   * @return bool
   *   True if banner popup is enabled.
   */
  public function isBannerPopup(): bool {
    return $this->bannerPopup;
  }

}
