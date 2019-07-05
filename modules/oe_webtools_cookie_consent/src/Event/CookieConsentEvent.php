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
   * A specific section or a subwebsite of main site.
   *
   * @var bool
   */
  protected $cckEnabled;

  /**
   * CookieConsentEvent constructor.
   */
  public function __construct() {
    // This is to prevent issues when serializing the object.
    // Those settings are temporary until a UI exists to set them.
    $this->setCckEnabled();
  }

  /**
   * {@inheritdoc}
   */
  public function setCckEnabled(bool $cckEnabled = TRUE): void {
    $this->cckEnabled = $cckEnabled;
  }

  /**
   * {@inheritdoc}
   */
  public function isCckEnabled(): bool {
    return $this->cckEnabled;
  }

}
