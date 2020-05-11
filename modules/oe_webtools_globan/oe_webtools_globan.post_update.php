<?php

/**
 * @file
 * Post update hooks for Webtools Global Banner.
 */

declare(strict_types = 1);

/**
 * Set sticky config for globan.
 */
function oe_webtools_globan_post_update_00001(): void {
  $config = \Drupal::configFactory()->getEditable('oe_webtools_globan.settings');

  if (is_null($config->get('sticky'))) {
    $config->set('sticky', FALSE);
  }

  $config->save();
}
