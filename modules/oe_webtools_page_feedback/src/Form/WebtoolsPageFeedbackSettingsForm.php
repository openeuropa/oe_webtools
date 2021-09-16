<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_page_feedback\Form;

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
    $form['feedback_form_id'] = [
      '#type' => 'number',
      '#title' => $this->t('Form ID'),
      '#description' => $this->t('Provide your webtools form ID.'),
      '#default_value' => $config->get('feedback_form_id'),
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
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('oe_webtools_page_feedback.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('feedback_form_id', $form_state->getValue('feedback_form_id'))
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
