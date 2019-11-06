<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_analytics_rules\Kernel;

use Drupal\system\Tests\Routing\MockAliasManager as CoreMockAliasManager;

/**
 * An easily configurable mock alias manager.
 *
 * This overrides the core MockAliasManager and ensures to return the original
 * path if an alias path doesn't exists, in accordance to AliasManagerInterface.
 */
class MockAliasManager extends CoreMockAliasManager {

  /**
   * {@inheritdoc}
   */
  public function getAliasByPath($path, $langcode = NULL): string {
    if ($path[0] !== '/') {
      throw new \InvalidArgumentException(sprintf('Source path %s has to start with a slash.', $path));
    }

    $langcode = $langcode ?: $this->defaultLanguage;
    $this->lookedUp[$path] = 1;

    if (isset($this->aliases[$path][$langcode])) {
      return $this->aliases[$path][$langcode];
    }

    return $path;
  }

}
