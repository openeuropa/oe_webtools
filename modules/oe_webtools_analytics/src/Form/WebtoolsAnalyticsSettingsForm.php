<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for module.
 */
class WebtoolsAnalyticsSettingsForm extends ConfigFormBase {

  /**
   * Name of the config being edited.
   */
  const CONFIGNAME = 'oe_webtools_analytics.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_webtools_analytics_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['siteID'] = [
      '#type' => 'number',
      '#title' => $this->t('Site ID'),
      '#default_value' => $this->config(static::CONFIGNAME)->get('siteID'),
      '#description' => $this->t('The site unique numeric identifier.'),
    ];
    $form['sitePath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site path'),
      '#default_value' => $this->config(static::CONFIGNAME)->get('sitePath'),
      '#description' => $this->t('The domain + root path without protocol.'),
    ];
    $form['instance'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Instance'),
      '#default_value' => $this->config(static::CONFIGNAME)->get('instance'),
      '#description' => $this->t('The server instance. e.g. testing, ec.europa.eu or europa.eu.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(static::CONFIGNAME)
      ->set('siteID', $form_state->getValue('siteID'))
      ->set('sitePath', $form_state->getValue('sitePath'))
      ->set('instance', $form_state->getValue('instance'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['oe_webtools_analytics.settings'];
  }

}
