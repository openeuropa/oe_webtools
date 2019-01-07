<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\ConfigContext;

/**
 * Class DrupalContext.
 */
class WebtoolsAnalyticsConfigContext extends ConfigContext {

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
   * Add aliases for behat tests.
   *
   * @param string $path
   *   Source url for aliases.
   * @param \Behat\Gherkin\Node\TableNode $aliasesTable
   *   Language and alias pairs.
   *
   * @Given Aliases available for the path :path:
   */
  public function aliasesAvailableForPath(string $path, TableNode $aliasesTable): void {
    /** @var \Drupal\Core\Path\AliasStorageInterface $path_alias_storage */
    $path_alias_storage = \Drupal::service('path.alias_storage');
    foreach ($aliasesTable->getHash() as $row) {
      $path_alias_storage->save($path, $row['url'], $row['languages']);
    }
  }

}
