<?php

/**
 * @file
 * Post update hooks for Webtools Laco Widget module.
 */

declare(strict_types = 1);

/**
 * Set new option for enabling/disabling LACO widget.
 */
function oe_webtools_laco_widget_post_update_00001(): void {
  $config = \Drupal::configFactory()->getEditable('oe_webtools_laco_widget.settings');
  if (is_null($config->get('enabled')) && $config->get('include')) {
    $config->set('enabled', TRUE);
  }
  $config->save();
}
