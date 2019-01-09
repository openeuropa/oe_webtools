<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class WebtoolsConfigContext.
 */
class WebtoolsConfigContext extends RawDrupalContext {

  /**
   * The config context.
   *
   * @var \Drupal\DrupalExtension\Context\ConfigContext
   */
  protected $configContext;

  /**
   * Gathers some other contexts.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The before scenario scope.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();
    $this->configContext = $environment->getContext('Drupal\DrupalExtension\Context\ConfigContext');
  }

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
    $this->configContext->setConfig('oe_webtools_analytics.settings', 'siteID', $id);
    $this->configContext->setConfig('oe_webtools_analytics.settings', 'sitePath', $sitepath);
  }

  /**
   * Backup configs that need to be reverted in AfterScenario.
   *
   * We don't actually want to change the values,
   * we're just ensuring existing values will be restored after scenario is run.
   *
   * @BeforeScenario @BackupLacoConfigs
   */
  public function backupLacoConfigs() {
    $name = 'oe_webtools_laco_widget.settings';

    $configs = $this->getDriver()->getCore()->configGet($name);
    foreach ($configs as $key => $value) {
      $this->configContext->setConfig($name, $key, $value);
    }
  }

}
