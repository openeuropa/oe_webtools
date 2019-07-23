<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Behat step definitions related to the oe_webtools_globan module.
 */
class WebtoolsGlobanContext extends RawDrupalContext {

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
   * Backup globan settings config to be restored in an after-scenario.
   *
   * @BeforeScenario @backup-globan-settings
   */
  public function backupGlobanConfigs(): void {
    $name = 'oe_webtools_globan.settings';
    $configs = $this->getDriver()->getCore()->configGet($name);
    foreach ($configs as $key => $value) {
      $this->configContext->setConfig($name, $key, $value);
    }
  }

  /**
   * Make sure that load.js has the correct query parameters.
   *
   * @param string $globan_option
   *   The globan option value.
   * @param string|null $globan_lang
   *   The globan language option value.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   *
   * @Then Webtools javascript loaded with globan option :globan_option
   * @Then Webtools javascript loaded with globan option :globan_option and language option :globan_lang
   */
  public function assertJsGlobanOption(string $globan_option, $globan_lang = NULL): void {
    $lang_option = $globan_lang ? '&lang=' . $globan_lang : '';
    $this->assertSession()->elementExists('css', 'script[src$="load.js?globan=' . $globan_option . $lang_option . '"]');
  }

}
