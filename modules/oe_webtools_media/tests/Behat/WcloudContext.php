<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_media\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Behat context for Wcloud.
 */
class WcloudContext extends RawDrupalContext {

  /**
   * Enables the Wcloud Mock.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The scope.
   *
   * @beforeScenario @wcloud
   */
  public function enableTestModule(BeforeScenarioScope $scope): void {
    \Drupal::service('module_installer')->install([
      'oe_webtools_media_http_mock',
    ]);
  }

  /**
   * Disables the Wcloud Mock.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The scope.
   *
   * @afterScenario @wcloud
   */
  public function disableTestModule(AfterScenarioScope $scope): void {
    \Drupal::service('module_installer')->uninstall([
      'oe_webtools_media_http_mock',
    ]);
  }

}
