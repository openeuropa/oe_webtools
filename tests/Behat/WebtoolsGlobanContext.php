<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use PHPUnit\Framework\Assert;

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
   * Asserts the Webtools Globan JSON snippet on the page.
   *
   * @param string $value
   *   The expected JSON string.
   *
   * @throws \Exception
   *
   * @Then the page should have globan json snippet :value
   */
  public function globanJsonContainsParameter(string $value): void {
    $xpath_query = "//script[@type='application/json'][text() = '" . addcslashes($value, '\\\'') . "']";
    $elements = $this->getSession()->getPage()->findAll('xpath', $xpath_query);
    Assert::assertCount(1, $elements);
  }

}
