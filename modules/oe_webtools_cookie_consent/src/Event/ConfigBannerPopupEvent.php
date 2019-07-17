<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\Event;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event fired when a page is displayed, in order to handle Cookie consent data.
 *
 * @see oe_webtools_cookie_consent_page_attachments()
 */
class ConfigBannerPopupEvent extends Event implements RefinableCacheableDependencyInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * This event allows you to set the Cookie consent variable.
   *
   * @Event Drupal\oe_webtools_cookie_consent\Event\WebtoolsImportDataEvent
   */
  public const NAME = 'oe_webtools_cookie_consent.data_collection_banner_popup';

  /**
   * The CCK configuration name.
   */
  public const CONFIG_NAME = 'oe_webtools_cookie_consent.settings';

  /**
   * Name of the variable in the CCK configuration.
   */
  public const BANNER_POPUP = 'banner_popup';

  /**
   * Whether the banner CCK loader is enabled or not.
   *
   * @var bool
   */
  protected $bannerPopup = TRUE;

  /**
   * CookieConsentEvent constructor.
   */
  public function __construct() {
    $this->setBannerPopup();
  }

  /**
   * {@inheritdoc}
   */
  public function setBannerPopup(bool $bannerPopup = TRUE): void {
    $this->bannerPopup = $bannerPopup;
  }

  /**
   * {@inheritdoc}
   */
  public function isBannerPopup(): bool {
    return $this->bannerPopup;
  }

}
