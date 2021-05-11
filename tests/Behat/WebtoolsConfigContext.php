<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Provides the webtools behat assertions.
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
   * Add aliases for Behat tests.
   *
   * @param string $path
   *   Source url for aliases.
   * @param \Behat\Gherkin\Node\TableNode $aliasesTable
   *   Language and alias pairs.
   *
   * @Given aliases available for the path :path:
   */
  public function aliasesAvailableForPath(string $path, TableNode $aliasesTable): void {
    $path_alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
    foreach ($aliasesTable->getHash() as $row) {
      $alias = $path_alias_storage->create([
        'path' => $path,
        'alias' => $row['url'],
        'langcode' => $row['languages'],
      ]);

      $alias->save();
    }
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
    $this->backupConfigs('oe_webtools_laco_widget.settings');
  }

  /**
   * Backup configs that need to be reverted in AfterScenario by ConfigContext.
   *
   * @BeforeScenario @BackupAnalyticsConfigs
   */
  public function backupAnalyticsConfigs() {
    $this->backupConfigs('oe_webtools_analytics.settings');
  }

  /**
   * Backup configs that need to be reverted in AfterScenario by ConfigContext.
   *
   * @BeforeScenario @BackupCookieConsentConfigs
   */
  public function backupCookieConsentConfigs() {
    $this->backupConfigs('oe_webtools_cookie_consent.settings');
  }

  /**
   * Backup configs that need to be reverted in AfterScenario by ConfigContext.
   *
   * @param string $name
   *   Name of the configuration.
   */
  protected function backupConfigs(string $name) {
    $configs = $this->getDriver()->getCore()->configGet($name);
    foreach ($configs as $key => $value) {
      $this->configContext->setConfig($name, $key, $value);
    }
  }

}
