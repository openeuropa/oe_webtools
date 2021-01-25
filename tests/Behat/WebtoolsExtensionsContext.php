<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Gherkin\Node\FeatureNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Context to handle extension dependencies of each Behat feature.
 */
class WebtoolsExtensionsContext extends RawDrupalContext {

  /**
   * Install the modules needed for this feature.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeFeatureScope $scope
   *   The feature scope.
   *
   * @BeforeFeature
   */
  public static function installModules(BeforeFeatureScope $scope): void {
    $modules = self::getModulesToInstall($scope->getFeature());
    if (!$modules) {
      return;
    }

    \Drupal::service('module_installer')->install($modules);
    drupal_flush_all_caches();
  }

  /**
   * Returns the module names marked for installing in a feature.
   *
   * @param \Behat\Gherkin\Node\FeatureNode $feature
   *   The test feature.
   *
   * @return string[]
   *   A list of module names marked for install.
   */
  protected static function getModulesToInstall(FeatureNode $feature): array {
    $modules = [];
    foreach ($feature->getTags() as $tag) {
      if (strpos($tag, 'install:') === 0) {
        $modules[] = substr($tag, 8);
      }
    }

    return $modules;
  }

  /**
   * Uninstall modules enabled though the feature.
   *
   * @param \Behat\Behat\Hook\Scope\AfterFeatureScope $scope
   *   The feature scope.
   *
   * @AfterFeature
   */
  public static function uninstallModules(AfterFeatureScope $scope): void {
    $modules = self::getModulesToInstall($scope->getFeature());
    if (!$modules) {
      return;
    }

    \Drupal::service('module_installer')->uninstall($modules);
  }

}
