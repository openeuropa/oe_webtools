<?php

/**
 * @file
 * Post update hooks for oe_webtools_page_feedback.
 */

declare(strict_types=1);

use Drupal\Component\Serialization\Json;

/**
 * Migrate the Form ID and Survey URL settings into the Embed code JSON.
 */
function oe_webtools_page_feedback_post_update_00001(): void {
  $config = \Drupal::configFactory()->getEditable('oe_webtools_page_feedback.settings');

  $feedback_form_id = $config->get('feedback_form_id');
  $survey = $config->get('survey');
  // Leave the embed code empty if there was nothing to migrate.
  if (!empty($feedback_form_id) || !empty($survey)) {
    // The "service" and "lang" parameters were previously added
    // automatically; they are now part of the embed code, with the language
    // requested through the [langcode] placeholder.
    $embed_code = ['service' => 'dff'];
    if (!empty($feedback_form_id)) {
      $embed_code['id'] = $feedback_form_id;
    }
    $embed_code['lang'] = '[langcode]';
    if (!empty($survey)) {
      $embed_code['survey'] = $survey;
    }
    $config->set('embed_code', Json::encode($embed_code));
  }

  $config->clear('feedback_form_id');
  $config->clear('survey');
  $config->save();
}
