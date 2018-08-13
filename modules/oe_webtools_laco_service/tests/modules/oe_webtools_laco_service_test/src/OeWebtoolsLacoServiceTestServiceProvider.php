<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service_test;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * A test service provider that removes the DefaultRequestPolicy service.
 *
 * This is needed because it hard codes two policies and we only need 1 of them.
 * So we replace it with its parent (chained collector service) and define the
 * NoSessionOpen policy as our own service (since that is not currently being
 * used as a collected service itself by core but hardcoded instead).
 *
 * This so that we can use the fast KernelTestBase to test our assumptions
 * that also take page caching into account.
 */
class OeWebtoolsLacoServiceTestServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    $definition = $container->getDefinition('page_cache_request_policy');
    $new = clone $definition;
    $container->removeDefinition('page_cache_request_policy');

    $new->setClass('Drupal\Core\PageCache\ChainRequestPolicy');
    $container->setDefinition('page_cache_request_policy', $new);
  }

}
