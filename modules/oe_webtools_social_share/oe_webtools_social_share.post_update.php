<?php

/**
 * @file
 * Post update hook for oe_webtools_social_share.
 */

declare(strict_types=1);

/**
 * Set default social share settings.
 */
function oe_webtools_social_share_post_update_00001_set_default_config(): void {
  $config_factory = \Drupal::configFactory();
  $config = $config_factory->getEditable('oe_webtools_social_share.settings');

  if (is_null($config->get('custom_networks'))) {
    $config->set('custom_networks', FALSE);
  }

  if (is_null($config->get('networks'))) {
    $config->set('networks', []);
  }

  $config->save(TRUE);
}
