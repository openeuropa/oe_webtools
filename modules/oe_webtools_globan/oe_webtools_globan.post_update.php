<?php

/**
 * @file
 * Post update hooks for Webtools Globan.
 */

declare(strict_types = 1);

/**
 * Set sticky config for globan.
 */
function oe_webtools_globan_post_update_00001(): void {
  $config_factory = \Drupal::configFactory();
  $config = $config_factory->getEditable('oe_webtools_globan.settings');

  if (is_null($config->get('sticky'))) {
    $config->set('sticky', FALSE);
  }

  $config->save();
}
