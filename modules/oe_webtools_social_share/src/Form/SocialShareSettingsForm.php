<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_social_share\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides configuration form for the Social share webtools widget.
 */
class SocialShareSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_social_share_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['oe_webtools_social_share.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('oe_webtools_social_share.settings');

    $form['icons'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display only icons'),
      '#description' => $this->t('Check this box if you would like to display only the icons without labels for the Social share block.'),
      '#default_value' => $config->get('icons'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('oe_webtools_social_share.settings')
      ->set('icons', $form_state->getValue('icons'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
