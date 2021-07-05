<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The form for the configuration of the Webtools Cookie Consent module.
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
    return 'oe_webtools_cookie_consent_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['banner_popup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the CCK banner.'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('banner_popup'),
      '#description' => $this->t('If checked, CCK will add a banner to your pages requesting the user to accept or refuse cookies on your site.'),
    ];

    $form['video_popup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable CCK video banner for the supported video elements.'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('video_popup'),
      '#description' => $this->t('If checked, CCK will alter the URL to go through the EC Cookie Consent service.'),
    ];

    $form['cookie_notice_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cookie Notice Page URL'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('cookie_notice_url'),
      '#description' => $this->t('The URL to the cookie notice page. The "{lang}" part of the URL will be automatically replaced by Webtools with the current language.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::CONFIG_NAME)
      ->set('banner_popup', $form_state->getValue('banner_popup'))
      ->set('video_popup', $form_state->getValue('video_popup'))
      ->set('cookie_notice_url', $form_state->getValue('cookie_notice_url'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [static::CONFIG_NAME];
  }

}
