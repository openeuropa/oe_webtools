<?php

/**
 * @file
 * Post update functions for the OpenEuropa Webtools Maps module.
 */

declare(strict_types = 1);

/**
 * Enable the OpenEuropa Webtools module.
 */
function oe_webtools_maps_post_update_enable_webtools() {
  \Drupal::service('module_installer')->install(['oe_webtools']);
}
