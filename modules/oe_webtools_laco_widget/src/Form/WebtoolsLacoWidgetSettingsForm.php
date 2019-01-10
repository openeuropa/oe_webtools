<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_widget\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for module.
 */
class WebtoolsLacoWidgetSettingsForm extends ConfigFormBase {

  /**
   * Name of the config being edited.
   */
  const CONFIGNAME = 'oe_webtools_laco_widget.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'oe_webtools_laco_widget_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['intro'] = [
      '#type' => 'item',
      '#description' => $this->t('For more information check the module <a href="@href">documentation</a>.', ['@href' => 'https://github.com/openeuropa/oe_webtools/blob/master/modules/oe_webtools_laco_widget/README.md']),
    ];

    $include_value = '';
    if (!empty($this->config(static::CONFIGNAME)->get('include'))) {
      $include_value = implode(PHP_EOL, $this->config(static::CONFIGNAME)->get('include'));
    }
    $form['include'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Include'),
      '#default_value' => $include_value,
      '#description' => $this->t('CSS selectors within which to apply the widget.'),
    ];

    $exclude_value = '';
    if (!empty($this->config(static::CONFIGNAME)->get('exclude'))) {
      $exclude_value = implode(PHP_EOL, $this->config(static::CONFIGNAME)->get('exclude'));
    }
    $form['exclude'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exclude'),
      '#default_value' => $exclude_value,
      '#description' => $this->t('CSS selectors to exclude the widget.'),
    ];
    $form['coverage'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Coverage'),
    ];
    $form['coverage']['coverage_document'] = [
      '#type' => 'select',
      '#title' => $this->t('Document'),
      '#options' => ['any' => 'any', 'other' => 'other', 'false' => 'false'],
      '#default_value' => $this->config(static::CONFIGNAME)->get('coverage.document'),
      '#description' => $this->t('Rule for links to (binary) documents.'),
    ];
    $form['coverage']['coverage_page'] = [
      '#type' => 'select',
      '#title' => $this->t('Page'),
      '#options' => ['any' => 'any', 'other' => 'other', 'false' => 'false'],
      '#default_value' => $this->config(static::CONFIGNAME)->get('coverage.page'),
      '#description' => $this->t('Rule for links to html pages.'),
    ];
    $form['icon'] = [
      '#type' => 'select',
      '#title' => $this->t('Icon'),
      '#options' => ['all' => 'all', 'dot' => 'dot'],
      '#default_value' => $this->config(static::CONFIGNAME)->get('icon'),
      '#description' => $this->t('Choose presentation of the LACO icon.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $form_values = $form_state->getValues();

    // Preprocess needed form values.
    $include_value = array_filter(preg_split("/\r\n/", $form_values['include']));
    $exclude_value = array_filter(preg_split("/\r\n/", $form_values['exclude']));
    $coverage_value = [
      'document' => $form_values['coverage_document'],
      'page' => $form_values['coverage_page'],
    ];

    $this->config(static::CONFIGNAME)
      ->set('include', $include_value)
      ->set('exclude', $exclude_value)
      ->set('coverage', $coverage_value)
      ->set('icon', $form_values['icon'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['oe_webtools_laco_widget.settings'];
  }

}
