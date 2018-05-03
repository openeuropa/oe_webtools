<?php

declare(strict_types = 1);

/**
 * Webtools WebtoolsImportSettingsEvent Event.
 *
 * @see https://webgate.ec.europa.eu/fpfis/wikis/pages/viewpage.action?spaceKey=webtools&title=Piwik
 */

namespace Drupal\oe_webtools_analytics\Event;

use Drupal\oe_webtools_analytics\Entity\WebtoolsAnalyticsSearch;
use Symfony\Component\EventDispatcher\Event;
use JsonSerializable;

/**
 * Class WebtoolsImportDataEvent.
 *
 * @package Drupal\oe_webtools_analytics\Event
 */
class WebtoolsImportSettingsEvent extends Event implements JsonSerializable {
  /**
   * This event allows you to set the Analytics variable.
   *
   * @Event Drupal\oe_webtools_analytics\Event\WebtoolsImportDataEvent
   */
  const NAME = 'webtools_analytics.data_collection';

  /**
   * The site ID (mandatory).
   *
   * @var string
   */
  private $siteId;

  /**
   * A specific section or a subwebsite of main site.
   *
   * @var string
   */
  private $siteSection;

  /**
   * Allows you to define the root path of your website.
   *
   * @var array
   */
  private $sitePath;

  /**
   * Set this variable to true on your 404 page.
   *
   * @var bool
   */
  private $is404Page;

  /**
   * Set this variable to true on your 403 page.
   *
   * @var bool
   */
  private $is403Page;

  /**
   * Allows to override or set the language of the current page (optional).
   *
   * @var string
   */
  private $langCode;

  /**
   * Allows to switch between these instances (optional).
   *
   *   - ec.europa.eu
   *   - europa.eu
   *   - testing.
   *
   * @var string
   */
  private $instance;

  /**
   * The Search result in json format.
   *
   * @var \Drupal\oe_webtools_analytics\Entity\WebtoolsAnalyticsSearch
   */
  private $search;

  /**
   * WebtoolsImportSettingsEvent constructor.
   */
  public function __construct() {
    $this->search = new WebtoolsAnalyticsSearch();
  }

  /**
   * Sets the site id.
   *
   * @param string $siteId
   *   It is a mandatory field type NUMBER and the default value "n/a".
   */
  public function setSiteId(string $siteId): void {
    $this->siteId = $siteId;
  }

  /**
   * Sets the sitePath, allowing to identify "outlinks" and "inlink".
   *
   * From other websites in the same domain.
   *
   * @param array $sitePath
   *   The value must be: domain (without protocol) + root path of the site.
   */
  public function setSitePath(array $sitePath): void {
    $this->sitePath = $sitePath;
  }

  /**
   * Sets the section or a subwebsite allowing to refine the statistics.
   *
   * @param string $siteSection
   *   An optional string with dafault value "n/a".
   */
  public function setSiteSection(string $siteSection): void {
    $this->siteSection = $siteSection;
  }

  /**
   * Sets to true on 404 page.
   *
   * @param bool $is404Page
   *   A boolean variable set as false by default.
   */
  public function setIs404Page(bool $is404Page = TRUE): void {
    $this->is404Page = $is404Page;
  }

  /**
   * Sets to true on 403 page.
   *
   * @param bool $is403Page
   *   A boolean variable set as false by default.
   */
  public function setIs403Page(bool $is403Page = TRUE): void {
    $this->is403Page = $is403Page;
  }

  /**
   * Allows you to override or set the language of the current page.
   *
   * @param string $langCode
   *   An optional string with "unknown" as default value.
   */
  public function setLangCode(string $langCode): void {
    $this->langCode = $langCode;
  }

  /**
   * Sets Instance to send the tracking information to different servers.
   *
   * Servers:
   *    https://webanalytics.ec.europa.eu
   *    https://webanalytics.europa.eu
   *    https://webgate.ec.europa.eu/fpfis/piwik.
   *
   * @param string $instance
   *   An optional string with "unknown" as default value.
   */
  public function setInstance(string $instance): void {
    $this->instance = $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    $data = [
      "utility" => "piwik",
      'siteID' => $this->siteId,
      'sitePath' => $this->sitePath,
      'siteSection' => $this->siteSection,
      'is404' => $this->is404Page,
      'is403' => $this->is403Page,
      'lang' => $this->langCode,
      'instance' => $this->instance,
    ];

    if ($this->search->isSetKeyword()) {
      $data['search'] = $this->search->jsonSerialize();
    }

    return array_filter($data);
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return json_encode($this);
  }

  /**
   * A mandatory field "siteId".
   *
   * @return bool
   *   Whether or not the siteId exists.
   */
  public function isValid() {
    // SiteId is required.
    if (!$this->siteId) {
      return FALSE;
    }
    return TRUE;
  }

}
