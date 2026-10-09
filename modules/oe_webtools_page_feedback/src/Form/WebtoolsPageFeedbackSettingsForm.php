<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_page_feedback\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides configuration form for the Page Feedback Form webtools widget.
 */
class WebtoolsPageFeedbackSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_page_feedback_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('oe_webtools_page_feedback.settings');

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#description' => $this->t('Check this box if you would like to enable the Page feedback form on this site.'),
      '#default_value' => $config->get('enabled'),
    ];
    $form['embed_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Embed code'),
      '#description' => $this->t('JSON-encoded parameters passed to the Page Feedback Form widget, for example <code>{"service": "dff", "id": "your-form-id"}</code>. Use the <code>[langcode]</code> placeholder for the current interface language, for example <code>{"service": "dff", "id": "your-form-id", "lang": "[langcode]"}</code>.'),
      '#default_value' => $config->get('embed_code'),
      '#states' => [
        'required' => [
          'input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    $embed_code = (string) $form_state->getValue('embed_code');
    if ($embed_code !== '' && !is_array(Json::decode($embed_code))) {
      $form_state->setErrorByName('embed_code', $this->t('The embed code must be valid JSON.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('oe_webtools_page_feedback.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('embed_code', $form_state->getValue('embed_code'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['oe_webtools_page_feedback.settings'];
  }

}
