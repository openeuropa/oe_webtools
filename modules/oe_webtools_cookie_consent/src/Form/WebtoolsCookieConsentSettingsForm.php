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
    return static::CONFIG_NAME;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['bannerPopup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the CCK banner.'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('banner_popup'),
      '#description' => $this->t('If checked, CCK will add a banner to your pages requesting the user to accept or refuse cookies on your site.'),
    ];

    $form['mediaOembedPopup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the override of Media oEmbed iframe.'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('media_oembed_popup'),
      '#description' => $this->t('If checked, CCK will alter the URL to go through the EC cookie consent service.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::CONFIG_NAME)
      ->set('banner_popup', $form_state->getValue('bannerPopup'))
      ->set('media_oembed_popup', $form_state->getValue('mediaOembedPopup'))
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
