<?php

/**
 * @file
 * Post update hook for oe_webtools_captcha.
 */

declare(strict_types=1);

/**
 * Update captcha validation endpoint.
 */
function oe_webtools_captcha_post_update_00001(): void {
  \Drupal::configFactory()->getEditable('oe_webtools_captcha.settings')
    ->set('validationEndpoint', 'https://webtools.europa.eu/rest/captcha/verify')
    ->save();
}
