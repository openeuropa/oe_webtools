<?php

/**
 * @file
 * Post update functions for OpenEuropa Webtools Global Banner module.
 */

declare(strict_types = 1);

/**
 * Provide banner visibility defaults.
 */
function oe_webtools_globan_post_update_visibility_defaults(): void {
  \Drupal::configFactory()->getEditable('oe_webtools_globan.settings')
    ->set('visibility.action', 'show')
    ->set('visibility.pages', '')
    ->save();
}
