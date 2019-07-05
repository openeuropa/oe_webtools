<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The form for the configuration of the Webtools Cookie consent module.
 */
class WebtoolsCookieConsentSettingsForm extends ConfigFormBase {

  /**
   * Name of the config being edited.
   */
  const CONFIG_NAME = 'oe_webtools_cookie_consent.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_cookie_consent_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['cckEnabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Webtools Cookie Consent Kit.'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('cckEnabled'),
      '#description' => $this->t('If checked, CCK will add a banner to your pages requesting the user to accept or refuse cookies on your site.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::CONFIG_NAME)
      ->set('cckEnabled', $form_state->getValue('cckEnabled'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['oe_webtools_cookie_consent.settings'];
  }

}
