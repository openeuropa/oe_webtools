<?php

/**
 * @file
 * Post update hook for oe_webtools_cookie_consent.
 */

declare(strict_types = 1);

/**
 * Set default cookie consent kit settings.
 */
function oe_webtools_cookie_consent_post_update_00001_set_default_config(): void {
  $config_factory = \Drupal::configFactory();
  $config = $config_factory->getEditable('oe_webtools_cookie_consent.settings');

  if (is_null($config->get('banner_popup'))) {
    $config->set('banner_popup', TRUE);
  }

  if (is_null($config->get('video_popup'))) {
    $config->set('video_popup', TRUE);
  }

  $config->save(TRUE);
}

/**
 * Add additional new dependency from OpenEuropa Webtools module.
 */
function oe_webtools_cookie_consent_post_update_00002(): void {
  \Drupal::service('module_installer')->install(['oe_webtools']);
}
