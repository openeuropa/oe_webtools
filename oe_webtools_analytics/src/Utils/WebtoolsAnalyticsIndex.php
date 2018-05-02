<?php

declare(strict_types = 1);

/**
 * Contains the json index that will be find on page when the script tag is rendered.
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
   *
   */
  const  UTILITY = 'utility';

  /**
   *  Allows you to refine your statistics by indicating a section  or a subwebsite of your site.
   */
  const  SITE_PATH = 'sitePath';

  /**
   * 
   */
  const  SITE_SECTION = 'siteSection';

}
