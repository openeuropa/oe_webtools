<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_captcha\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The form for managing the configuration of the Webtools Captcha module.
 */
class WebtoolsCaptchaSettingsForm extends ConfigFormBase {

  /**
   * Name of the config being edited.
   */
  const CONFIG_NAME = 'oe_webtools_captcha.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_captcha_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['validationEndpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Validation endpoint ID'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('validationEndpoint'),
      '#description' => $this->t('The URL of the captcha validation endpoint.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::CONFIG_NAME)
      ->set('validationEndpoint', $form_state->getValue('validationEndpoint'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['oe_webtools_captcha.settings'];
  }

}
