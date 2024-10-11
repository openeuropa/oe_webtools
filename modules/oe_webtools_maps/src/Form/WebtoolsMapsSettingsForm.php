<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_maps\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides configuration form for the Maps webtools widget.
 */
class WebtoolsMapsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_maps_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('oe_webtools_maps.settings');

    $form['map_version'] = [
      '#type' => 'select',
      '#title' => $this->t('Map Version'),
      '#options' => [
        '2.0' => $this->t('Version 2.0'),
        '3.0' => $this->t('Version 3.0'),
      ],
      '#default_value' => $config->get('map_version') ?? '2.0',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('oe_webtools_maps.settings')
      ->set('map_version', $form_state->getValue('map_version'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['oe_webtools_maps.settings'];
  }

}
