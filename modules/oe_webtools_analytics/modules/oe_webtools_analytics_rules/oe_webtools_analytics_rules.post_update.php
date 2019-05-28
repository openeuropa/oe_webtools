<?php

/**
 * @file
 * Post update functions for OpenEuropa Webtools Analytics Rules module.
 */

declare(strict_types = 1);

/**
 * Set default weight for 'webtools_analytics_rule' config entities.
 */
function oe_webtools_analytics_rules_post_update_rules_default_weight(): void {
  $configs = \Drupal::entityTypeManager()->getStorage('webtools_analytics_rule')->loadMultiple();
  foreach ($configs as $config) {
    if ($config->get('weight') === NULL) {
      $config->set('weight', -10);
      $config->save();
    }
  }
}
