<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\oe_webtools_analytics\Search\SearchParametersInterface;

/**
 * Provides an interface for AnalyticsEvent accordingly to the documentation.
 */
interface AnalyticsEventInterface extends \JsonSerializable, RefinableCacheableDependencyInterface {

  /**
   * The Webtools analytics entrance in settings.php.
   */
  public const CONFIG_NAME = 'oe_webtools_analytics.settings';

  /**
   * The site unique identifier.
   */
  public const SITE_ID = 'siteID';

  /**
   * Representing the 403 key in settings.
   */
  public const IS403 = 'is403';

  /**
   * Representing the 404 key in settings.
   */
  public const IS404 = 'is404';

  /**
   * The current page language.
   */
  public const LANG = 'lang';

  /**
   * The analytics tools name, for e.g: piwik.
   */
  public const UTILITY = 'utility';

  /**
   * The domain + root path without protocol.
   */
  public const SITE_PATH = 'sitePath';

  /**
   * Allows to send the tracking information from different servers.
   */
  public const INSTANCE = 'instance';

  /**
   * Refine the statistics by indicating a site section  or a subwebsite.
   */
  public const SITE_SECTION = 'siteSection';
  /**
   * Set to true when search with the parameters form SearchParameters class.
   *
   * @see \Drupal\oe_webtools_analytics\Search\SearchParametersInterface
   */
  public const SEARCH = 'search';

  /**
   * Set the search parameters object.
   *
   * @param \Drupal\oe_webtools_analytics\Search\SearchParametersInterface $searchParameters
   *   The search parameters object.
   */
  public function setSearchParameters(SearchParametersInterface $searchParameters): void;

  /**
   * Sets the site id.
   *
   * @param string $siteId
   *   It is a mandatory field type NUMBER and the default value "n/a".
   */
  public function setSiteId(string $siteId = 'n/a'): void;

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
   *   An optional string with default value "n/a".
   */
  public function setSiteSection(string $siteSection = 'n/a'): void;

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
  public function setLangCode(string $langCode = 'unknown'): void;

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
  public function setInstance(string $instance = ''): void;

  /**
   * Sets the utility parameter.
   *
   * @param string $utility
   *   By default is set to "piwik".
   */
  public function setUtility(string $utility = 'piwik'): void;

  /**
   * Get siteID.
   *
   * @return string
   *   The unique number for each site.
   */
  public function getSiteId(): string;

  /**
   * Get the section.
   *
   * @return string
   *   The section or subsection within page.
   */
  public function getSiteSection(): string;

  /**
   * Get the sitePath.
   *
   * @return array
   *   A set of domain without protocol + root path to the site.
   */
  public function getSitePath(): array;

  /**
   * Get whether or not is a 404 page.
   *
   * @return bool
   *   True in case is 404 page otherwise false.
   */
  public function is404Page(): bool;

  /**
   * Get whether or not is a 403 page.
   *
   * @return bool
   *   True in case is 403 page otherwise false.
   */
  public function is403Page(): bool;

  /**
   * Get the site language.
   *
   * @return string
   *   The current page language.
   */
  public function getLangCode(): string;

  /**
   * Get the instance.
   *
   * @return string
   *   An optional string, e.g: testing.
   */
  public function getInstance(): string;

  /**
   * An object which will give json structure with some parameters.
   *
   * @return \Drupal\oe_webtools_analytics\Search\SearchParametersInterface
   *   An instance of SearchParameters class.
   */
  public function getSearch(): SearchParametersInterface;

  /**
   * Get the utility parameter.
   *
   * @return string
   *   A string which by default is "piwik"
   */
  public function getUtility(): string;

  /**
   * A mandatory field "siteId".
   *
   * @return bool
   *   Whether or not the siteId exists.
   */
  public function isValid(): bool;

}
