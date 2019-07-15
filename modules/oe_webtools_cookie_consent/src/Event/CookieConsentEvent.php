<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\Event;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\oe_webtools_cookie_consent\CookieConsentEventInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event fired when a page is displayed, in order to handle Cookie consent data.
 *
 * @see oe_webtools_cookie_consent_page_attachments()
 */
class CookieConsentEvent extends Event implements CookieConsentEventInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * This event allows you to set the Cookie consent variable.
   *
   * @Event Drupal\oe_webtools_cookie_consent\Event\WebtoolsImportDataEvent
   */
  public const NAME = 'webtools_cookie_consent.data_collection';

  /**
   * Whether the banner CCK loader is enabled or not.
   *
   * @var bool
   */
  protected $bannerPopup;

  /**
   * Whether the override of Media Oembed is enabled or not.
   *
   * @var bool
   */
  protected $mediaOembedPopup;

  /**
   * CookieConsentEvent constructor.
   */
  public function __construct() {
    $this->setBannerPopup(TRUE);
    $this->setMediaOembedPopup(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function setBannerPopup(bool $bannerPopup): void {
    $this->bannerPopup = $bannerPopup;
  }

  /**
   * {@inheritdoc}
   */
  public function setMediaOembedPopup(bool $mediaOembedPopup): void {
    $this->mediaOembedPopup = $mediaOembedPopup;
  }

  /**
   * {@inheritdoc}
   */
  public function isBannerPopup(): bool {
    return $this->bannerPopup;
  }

  /**
   * {@inheritdoc}
   */
  public function isMediaOembedPopup(): bool {
    return $this->mediaOembedPopup;
  }

}
