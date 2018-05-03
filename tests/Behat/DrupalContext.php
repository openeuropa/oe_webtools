<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Gherkin\Node\TableNode;

/**
 * Behat with module, feature and feature_set management.
 */
class DrupalContext extends RawDrupalContext {
  /**
   * A ModuleHandler instance.
   *
   * @var Drupal\Core\Extension\ModuleHandler
   */
  private $moduleHandler;

  /**
   * DrupalContext constructor.
   */
  public function __construct() {
    $this->moduleHandler = \Drupal::moduleHandler();
  }

  /**
   * Refresh the list of module.
   *
   * Before all scenario, we need to run module_list to refresh system_list,
   * which is needed because of memory issues.
   *
   * @BeforeScenario
   */
  public function refreshDefaultEnabledModules() {
    $this->moduleHandler->getModuleList();
  }

  /**
   * Enables one or more modules.
   *
   * Provide modules data in the following format:
   *
   * | modules  |
   * | blog     |
   * | book     |
   *
   * @param \Behat\Gherkin\Node\TableNode $modules_table
   *   The table listing modules.
   *
   * @Given the/these module/modules is/are enabled
   */
  public function theseModulesAreEnabled(TableNode $modules_table) {
    $cache_flushing = FALSE;
    $message = [];

    foreach ($modules_table->getHash() as $row) {
      if (!$this->moduleHandler->moduleExists($row['modules'])) {
        $message[] = $row['modules'];
      }
      else {
        $cache_flushing = TRUE;
      }
    }

    if (!empty($message)) {
      throw new \Exception(sprintf('Module "%s" not correctly enabled', implode(', ', $message)));
    }

    if ($cache_flushing) {
      drupal_flush_all_caches();
    }
  }

  /**
   * Disables one or more modules.
   *
   * Provide modules data in the following format:
   *
   * | modules  |
   * | blog     |
   * | book     |
   *
   * @param \Behat\Gherkin\Node\TableNode $modules_table
   *   The table listing modules.
   *
   * @Given the/these module/modules is/are not enabled
   */
  public function disableModule(TableNode $modules_table) {
    foreach ($modules_table->getHash() as $row) {
      if (!$this->moduleHandler->moduleExists($row['modules'])) {
        \Drupal::service('module_installer')->uninstall($row['modules']);
      }
    }
  }

}
