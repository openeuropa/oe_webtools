<?php

declare(strict_types = 1);

/**
 * Contains the json index.
 *
 * It will be found on the page when the analytic script tag is rendered.
 *
 * @see https://webgate.ec.europa.eu/fpfis/wikis/pages/viewpage.action?spaceKey=webtools&title=Piwik
 */

namespace Drupal\oe_webtools_analytics\Utils;

/**
 * Class WebtoolsAnalyticsSearch.
 *
 * @package Drupal\oe_webtools_analytics\Entity
 */
class WebtoolsAnalyticsIndex {

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
   * The analytics tools name, for e.g: piwik.
   */
  const  UTILITY = 'utility';

  /**
   * The domain + root path without protocol.
   */
  const  SITE_PATH = 'sitePath';

  /**
   * Refine the statistics by indicating a site section  or a subwebsite.
   */
  const  SITE_SECTION = 'siteSection';

}
