<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Drupal\DrupalExtension\Context\ConfigContext;

/**
 * Class WebtoolsConfigContext.
 */
class WebtoolsConfigContext extends ConfigContext {

  /**
   * Apply settings for the Webtools Analytics configuration.
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

  /**
   * Backup configs that need to be reverted in AfterScenario.
   *
   * @BeforeScenario @BackupLacoConfigs
   */
  public function backupLacoConfigs() {

    $name = 'oe_webtools_laco_widget.settings';

    $configs = $this->getDriver()->getCore()->configGet($name);
    foreach ($configs as $key => $backup) {
      $this->config[$name][$key] = $backup;
    }
  }

}
