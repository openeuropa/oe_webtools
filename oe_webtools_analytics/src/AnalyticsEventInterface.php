<?php

namespace Drupal\oe_webtools_analytics;

use JsonSerializable;
use Drupal\oe_webtools_analytics\Entity\SearchParametersInterface;

interface AnalyticsEventInterface extends JsonSerializable {

  /**
   * The site unique identifier.
   */
  const SITE_ID = 'siteID';

  /**
   * Representing the 403 key in settings.
   */
  const  IS403 = 'is403';

  /**
   * Representing the 404 key in settings.
   */
  const  IS404 = 'is404';

  /**
   * The current page language.
   */
  const  LANG = 'lang';

  /**
   * The analytics tools name, for e.g: piwik.
   */
  const  UTILITY = 'utility';

  /**
   * The domain + root path without protocol.
   */
  const  SITE_PATH = 'sitePath';

  /**
   *  Allows to send the tracking information from different servers.
   */
  const  INSTANCE = 'instance';

  /**
   * Refine the statistics by indicating a site section  or a subwebsite.
   */
  const  SITE_SECTION = 'siteSection';
  /**
   * This variable is set to true when search with the parameters form SearchParameters class.
   *
   * @see \Drupal\oe_webtools_analytics\Entity\SearchParametersInterface
   */
  const  SEARCH = 'search';

  /**
   * Sets the site id.
   *
   * @param string $siteId
   *   It is a mandatory field type NUMBER and the default value "n/a".
   */
  public function setSiteId(string $siteId): void;

  /**
   * Sets the sitePath, allowing to identify "outlinks" and "inlink".
   *
   * From other websites in the same domain.
   *
   * @param array $sitePath
   *   The value must be: domain (without protocol) + root path of the site.
   */
  public function setSitePath(array $sitePath): void;

  /**
   * Sets the section or a subwebsite allowing to refine the statistics.
   *
   * @param string $siteSection
   *   An optional string with dafault value "n/a".
   */
  public function setSiteSection(string $siteSection): void;

  /**
   * Sets to true on 404 page.
   *
   * @param bool $is404Page
   *   A boolean variable set as false by default.
   */
  public function setIs404Page(bool $is404Page = FALSE): void;

  /**
   * Sets to true on 403 page.
   *
   * @param bool $is403Page
   *   A boolean variable set as false by default.
   */
  public function setIs403Page(bool $is403Page = FALSE): void;

  /**
   * Allows you to override or set the language of the current page.
   *
   * @param string $langCode
   *   An optional string with "unknown" as default value.
   */
  public function setLangCode(string $langCode): void;

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
  public function setInstance(string $instance): void;

  /**
   * Sets the utility parameter, eg: piwik.
   *
   * @param string $utility
   */
  public function setUtility(string $utility = 'piwik'): void;

  /**
   * Get siteID.
   *
   * @return string
   */
  public function getSiteId(): string;

  /**
   * Get the section.
   *
   * @return string
   */
  public function getSiteSection(): string;

  /**
   * Get the sitePath.
   *
   * @return array
   */
  public function getSitePath(): array;

  /**
   * Get whether or not is a 404 page.
   *
   * @return bool
   */
  public function is404Page(): bool;

  /**
   * Get whether or not is a 404 page.
   *
   * @return bool
   */
  public function is403Page(): bool;

  /**
   * Get the site language.
   *
   * @return string
   */
  public function getLangCode(): string;

  /**
   * Get the instance.
   *
   * @return string
   */
  public function getInstance(): string;

  /**
   * An instance of SearchParameters class.
   *
   * @return SearchParametersInterface
   */
  public function getSearch(): SearchParametersInterface;

  /**
   * Get the utility parameter,by default is "piwik".
   *
   * @return string
   */
  public function getUtility(): string;

  /**
   * A mandatory field "siteId".
   *
   * @return bool
   *   Whether or not the siteId exists.
   */
  public function isValid() : bool;
}
