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
class WebtoolsExtentionsContext extends RawDrupalContext {

  /**
   * The modules list used for particular .
   *
   * @var array
   */
  protected static $modules = [];

  /**
   * Collect the module names enabled for feature execution.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeFeatureScope $scope
   *   The feature scope.
   *
   * @BeforeFeature
   */
  public static function collectEnabledModules(BeforeFeatureScope $scope): void {
    // Reset the module list at the beginning of each feature.
    self::$modules = [];

    self::$modules = self::getModulesToInstall($scope->getFeature());
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
    $modules_initially_enabled = array_keys(\Drupal::service('module_handler')->getModuleList());
    foreach ($feature->getTags() as $tag) {
      if (strpos($tag, 'install:') === 0) {
        \Drupal::service('module_installer')->install(array_map('trim', explode(',', substr($tag, 8))));
      }
    }

    $modules_enabled = array_keys(\Drupal::service('module_handler')->getModuleList());
    return array_diff($modules_enabled, $modules_initially_enabled);
  }

  /**
   * Uninstall modules enabled though the feature.
   *
   * @param \Behat\Behat\Hook\Scope\AfterFeatureScope $scope
   *   The feature scope.
   *
   * @AfterFeature
   */
  public static function uninstallEnabledModules(AfterFeatureScope $scope): void {
    if (self::$modules) {
      \Drupal::service('module_installer')->uninstall(self::$modules);
    }
  }

}
