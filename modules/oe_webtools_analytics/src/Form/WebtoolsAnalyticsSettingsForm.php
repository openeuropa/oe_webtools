<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_analytics\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The form for managing the configuration of the Webtools Analytics module.
 */
class WebtoolsAnalyticsSettingsForm extends ConfigFormBase {

  /**
   * Name of the config being edited.
   */
  const CONFIG_NAME = 'oe_webtools_analytics.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_analytics_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['siteID'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site ID'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('siteID'),
      '#description' => $this->t('The site unique alpha numeric identifier.'),
    ];
    $form['sitePath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site path'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('sitePath'),
      '#description' => $this->t('The domain + root path without protocol.'),
    ];

    $instance_options = [
      'ec.europa.eu' => $this->t('ec.europa.eu - for European Commission websites'),
      'europa.eu' => $this->t('europa.eu - for inter-institutional websites'),
      'testing' => $this->t('testing - for testing purposes'),
    ];

    $instance_value = $this->config(static::CONFIG_NAME)->get('instance');

    $form['instance'] = [
      '#type' => 'radios',
      '#title' => $this->t('Europa Analytics server instance'),
      '#options' => $instance_options,
      // Default to ec.europa.eu if no value is configured.
      '#default_value' => $instance_value ?: 'ec.europa.eu',
      '#description' => $this->t('The Europa Analytics server to report analytics data to.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::CONFIG_NAME)
      ->set('siteID', $form_state->getValue('siteID'))
      ->set('sitePath', $form_state->getValue('sitePath'))
      ->set('instance', $form_state->getValue('instance'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['oe_webtools_analytics.settings'];
  }

}
