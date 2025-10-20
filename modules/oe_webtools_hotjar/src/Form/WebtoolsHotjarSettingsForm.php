<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_hotjar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The form for the configuration of the Webtools Hotjar module.
 */
class WebtoolsHotjarSettingsForm extends ConfigFormBase {

  /**
   * Name of the config being edited.
   */
  const CONFIG_NAME = 'oe_webtools_hotjar.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_hotjar_configure_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Hotjar.'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('enabled'),
      '#description' => $this->t('If checked, the Hotjar script will be printed on all pages for anonymous users.'),
    ];

    $form['site'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site ID'),
      '#default_value' => $this->config(static::CONFIG_NAME)->get('site'),
      '#description' => $this->t('The site to be tracked.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config(static::CONFIG_NAME)
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('site', $form_state->getValue('site'))
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
