<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Drupal\DrupalExtension\Context\ConfigContext;

/**
 * Class DrupalContext.
 */
class WebtoolsAnalyticsConfigContext extends ConfigContext {

  /**
   * Configures the CAS module to use Drupal login.
   *
   * @param string $id
   *   ID for the Webtools Analytics.
   * @param string $sitepath
   *   Site path for the Webtools Analytics.
   *
   * @Given the Webtools Analytics configuration is set to use the id :id and the site path :sitepath
   */
  public function webtoolsAnalyicsConfigIsSet(string $id, string $sitepath): void {
    $this->setConfig('oe_webtools_analytics.settings', 'siteID', $id);
    $this->setConfig('oe_webtools_analytics.settings', 'sitePath', $sitepath);
  }

}
