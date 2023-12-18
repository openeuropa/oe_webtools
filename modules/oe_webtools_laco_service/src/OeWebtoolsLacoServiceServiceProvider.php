<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Sets the Drupal 10 version of the middleware service if needed.
 */
class OeWebtoolsLacoServiceServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    $definition = $container->getDefinition('oe_webtools_laco_service.service_middleware');
    $definition->setClass('\Drupal\oe_webtools_laco_service\StackMiddleware\LacoServiceMiddlewareDrupal10');
  }

}
